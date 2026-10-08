<?php

namespace App\Http\Controllers\Cmi;

use App\Http\Controllers\Controller;
use App\Models\CmiTableImage;
use App\Models\FormatTemplate;
use App\Models\ReportSubmission;
use App\Models\ReportTable;
use App\Models\ReportTableDoc;
use App\Models\User;
use DateTime;
use DateTimeZone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TableController extends Controller
{
    public function load(Request $request): JsonResponse
    {
        $cmiParam      = $request->input('cmi_user_id');
        $userId        = $cmiParam ? (int) $cmiParam : (Auth::id() ?? session('user_id'));
        $reportingYear = (int) ($request->input('year') ?? date('Y'));
        $tableNo       = preg_replace('/[^A-Za-z0-9]/', '', $request->input('table_no') ?? 'T1');

        if (!$tableNo) {
            return response()->json(['error' => 'Missing table_no']);
        }

        $canonicalMap = [];
        foreach (config('secreco.all_tables', []) as $stTable) {
            $canonicalMap[strtoupper($stTable)] = $stTable;
        }
        $tableNo = $canonicalMap[strtoupper($tableNo)] ?? $tableNo;

        // Load from all users belonging to this institution, picking the best/newest filled record.
        $mateIds  = $this->getInstMateIds($userId);
        $allIds   = array_merge([$userId], $mateIds);

        $priority = ['accepted' => 4, 'done' => 3, 'submitted' => 3, 'returned' => 2.5, 'draft' => 2, 'not-started' => 0, 'deleted' => 0];
        $cleanNo = ltrim($tableNo, 'Tt');
        $candidates = array_values(array_unique(array_filter([
            $tableNo,
            strtoupper($tableNo),
            strtolower($tableNo),
            preg_replace('/[^A-Za-z0-9]/', '', $tableNo),
            strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $tableNo)),
            $cleanNo,
            'T' . $cleanNo,
            't' . $cleanNo,
        ])));
        $allRows  = ReportTable::whereIn('user_id', $allIds)
            ->where('reporting_year', $reportingYear)
            ->whereIn('table_no', $candidates)
            ->get();

        $row = null;
        foreach ($allRows as $r) {
            if (!$row) {
                $row = $r;
                continue;
            }
            $pCurrent = $priority[$r->status] ?? 0;
            $pBest    = $priority[$row->status] ?? 0;

            if ($pCurrent > $pBest) {
                $row = $r;
            } elseif ($pCurrent === $pBest) {
                $cCurrent = $this->hasContent($r->meta_json, $r->rows_json);
                $cBest    = $this->hasContent($row->meta_json, $row->rows_json);
                if ($cCurrent && !$cBest) {
                    $row = $r;
                } elseif ($cCurrent === $cBest) {
                    $tCurrent = $r->updated_at ? strtotime((string)$r->updated_at) : 0;
                    $tBest    = $row->updated_at ? strtotime((string)$row->updated_at) : 0;
                    if ($tCurrent > $tBest) {
                        $row = $r;
                    }
                }
            }
        }

        // Docs: load from all users belonging to this institution
        $docCandidates = array_unique(array_merge($candidates, [$tableNo, 'TABLE ' . $tableNo, 'Table ' . $tableNo]));
        $images = ReportTableDoc::whereIn('user_id', $allIds)
            ->where('reporting_year', $reportingYear)
            ->whereIn('table_no', $docCandidates)
            ->orderBy('sort_order', 'asc')
            ->orderBy('uploaded_at', 'asc')
            ->get(['id', 'file_path', 'caption'])
            ->map(function ($img) {
                $path = $img->file_path ? '/' . ltrim($img->file_path, '/') : '';
                return [
                    'id'        => (int) $img->id,
                    'doc_id'    => (int) $img->id,
                    'file_path' => $path,
                    'caption'   => $img->caption ?? '',
                ];
            });

        // Query open remarks for this table
        $openRemark = \App\Models\CorrectionRequest::whereIn('cmi_user_id', $allIds)
            ->where('reporting_year', $reportingYear)
            ->whereIn('table_no', $candidates)
            ->where('status', 'open')
            ->orderByDesc('id')
            ->value('reason');

        // Check fallback to ReportSubmission snapshot if row is missing or rows_json is empty
        $rowRows = is_array($row?->rows_json) ? array_values($row->rows_json) : (is_object($row?->rows_json) ? array_values((array)$row->rows_json) : []);
        $meta    = is_array($row?->meta_json) ? $row->meta_json : (is_object($row?->meta_json) ? (array)$row->meta_json : []);

        if ((!$row || empty($rowRows)) && !empty($allIds)) {
            $latestSub = ReportSubmission::whereIn('user_id', $allIds)
                ->where('reporting_year', $reportingYear)
                ->orderByDesc('id')
                ->first();

            if ($latestSub && is_array($latestSub->snapshot_json)) {
                $snap = $latestSub->snapshot_json;
                $snapEntry = null;
                foreach ($candidates as $cand) {
                    if (isset($snap[$cand]) && is_array($snap[$cand])) {
                        $snapEntry = $snap[$cand];
                        break;
                    }
                }

                if ($snapEntry && (!empty($snapEntry['rows']) || !empty($snapEntry['meta']))) {
                    $snapRows = is_array($snapEntry['rows']) ? array_values($snapEntry['rows']) : (is_object($snapEntry['rows']) ? array_values((array)$snapEntry['rows']) : []);
                    $snapMeta = is_array($snapEntry['meta']) ? $snapEntry['meta'] : (is_object($snapEntry['meta']) ? (array)$snapEntry['meta'] : []);
                    $snapStatus = $snapEntry['status'] ?? ($row ? $row->status : 'accepted');
                    $snapSubmittedAt = $snapMeta['submitted_at'] ?? ($latestSub->submitted_at ? (is_string($latestSub->submitted_at) ? $latestSub->submitted_at : $latestSub->submitted_at->toDateTimeString()) : null);
                    $snapUpdatedAt = $snapEntry['updated_at'] ?? $snapSubmittedAt;

                    if ($images->isEmpty() && !empty($snapEntry['docs']) && is_array($snapEntry['docs'])) {
                        $images = collect($snapEntry['docs'])->map(function ($img) {
                            $p = is_array($img) ? ($img['file_path'] ?? '') : '';
                            $p = $p ? '/' . ltrim($p, '/') : '';
                            return [
                                'id'        => (int) ($img['id'] ?? $img['doc_id'] ?? 0),
                                'doc_id'    => (int) ($img['id'] ?? $img['doc_id'] ?? 0),
                                'file_path' => $p,
                                'caption'   => $img['caption'] ?? '',
                            ];
                        });
                    }

                    if (empty($openRemark) && !empty($latestSub->remarks) && in_array($snapStatus, ['returned'], true)) {
                        $openRemark = $latestSub->remarks;
                    }

                    return response()->json([
                        'status'       => $snapStatus,
                        'meta'         => $snapMeta,
                        'submitted_at' => $snapSubmittedAt,
                        'rows'         => $snapRows,
                        'updated_at'   => $snapUpdatedAt,
                        'docs'         => $images,
                        'remarks'      => $openRemark,
                    ]);
                }
            }
        }

        if (!$row) {
            return response()->json([
                'status'       => 'not-started',
                'meta'         => null,
                'rows'         => [],
                'updated_at'   => null,
                'submitted_at' => null,
                'docs'         => $images,
                'remarks'      => $openRemark,
            ]);
        }

        if ($images->isEmpty() && !empty($meta['images']) && is_array($meta['images'])) {
            $images = collect($meta['images'])->map(function ($img) {
                $p = is_array($img) ? ($img['file_path'] ?? '') : '';
                $p = $p ? '/' . ltrim($p, '/') : '';
                return [
                    'id'        => (int) ($img['id'] ?? $img['doc_id'] ?? 0),
                    'doc_id'    => (int) ($img['id'] ?? $img['doc_id'] ?? 0),
                    'file_path' => $p,
                    'caption'   => $img['caption'] ?? '',
                ];
            });
        }
        if (is_array($meta)) {
            unset($meta['images']);
        }

        $resStatus = $row->status;
        if ($resStatus === 'deleted') {
            $resStatus = 'not-started';
        }

        return response()->json([
            'status'       => $resStatus,
            'meta'         => $meta,
            'submitted_at' => $row->meta_json['submitted_at'] ?? null,
            'rows'         => $rowRows,
            'updated_at'   => $row->updated_at ? $row->updated_at->toDateTimeString() : null,
            'docs'         => $images,
            'remarks'      => $openRemark,
        ]);
    }

    public function save(Request $request): JsonResponse
    {
        $body = $request->json()->all() ?: $request->all();
        if (!$body) {
            return response()->json(['error' => 'Invalid JSON']);
        }

        $cmiParam      = $body['cmi_user_id'] ?? $request->input('cmi_user_id');
        $userId        = $cmiParam ? (int) $cmiParam : (Auth::id() ?? session('user_id'));
        $mateIds       = $this->getInstMateIds($userId);
        $allIds        = array_merge([$userId], $mateIds);
        $reportingYear = (int) ($body['year'] ?? date('Y'));
        $tableNo       = preg_replace('/[^A-Za-z0-9]/', '', $body['table_no'] ?? '');
        $meta          = $body['meta'] ?? new \stdClass();
        $rows          = $body['rows'] ?? [];
        $requested     = in_array($body['status'] ?? '', ['not-started', 'draft', 'done', 'error'], true) ? $body['status'] : 'draft';

        $canonicalMap = [];
        foreach (config('secreco.all_tables', []) as $stTable) {
            $canonicalMap[strtoupper($stTable)] = $stTable;
        }
        $tableNo = $canonicalMap[strtoupper($tableNo)] ?? $tableNo;

        $cleanNo = ltrim($tableNo, 'Tt');
        $candidates = array_values(array_unique(array_filter([
            $tableNo,
            strtoupper($tableNo),
            strtolower($tableNo),
            $cleanNo,
            'T' . $cleanNo,
            't' . $cleanNo,
        ])));

        // Check if table template exists
        $ft = \App\Models\FormatTemplate::where('year', $reportingYear)->whereIn('table_no', $candidates)->first();
        if ($ft) {
            $tableNo = $ft->table_no;
        }

        // Check if table is locked for CMI
        if ($ft && $ft->is_locked) {
            $currentUserRole = Auth::user()?->role;
            if ($currentUserRole !== 'pta') {
                return response()->json([
                    'success' => false,
                    'error'   => "Table {$tableNo} is locked for CMI. Only PTA administrators can input or fill out this table.",
                ], 403);
            }
        }

        $status = $requested;
        if ($status === 'draft' && !$this->hasContent($meta, $rows)) {
            $status = 'not-started';
        }

        // Preserve any previously stored submitted_at in meta_json so saving doesn't wipe out the original submission date
        $existingTable = ReportTable::where('user_id', $userId)
            ->where('reporting_year', $reportingYear)
            ->whereIn('table_no', $candidates)
            ->first();
        if ($existingTable && is_array($existingTable->meta_json) && !empty($existingTable->meta_json['submitted_at'])) {
            if (is_array($meta)) {
                $meta['submitted_at'] = $existingTable->meta_json['submitted_at'];
            } elseif (is_object($meta)) {
                $meta->submitted_at = $existingTable->meta_json['submitted_at'];
            }
        }

        $updatedAt = now();
        $updatedAtStr = $updatedAt->toDateTimeString();
        $isDraft      = ($requested === 'draft' || $status === 'draft');

        $wasReturned = ($existingTable && $existingTable->status === 'returned');
        if ($wasReturned && !$isDraft && $status === 'done') {
            if (is_array($meta)) {
                $meta['resubmitted_at'] = $updatedAtStr;
            } elseif (is_object($meta)) {
                $meta->resubmitted_at = $updatedAtStr;
            }
        }

        ReportTable::updateOrCreate(
            ['user_id' => $userId, 'reporting_year' => $reportingYear, 'table_no' => $tableNo],
            [
                'meta_json'  => $meta,
                'rows_json'  => $rows,
                'status'     => $status,
                'updated_at' => $updatedAt,
            ]
        );

        $updatedAtStr = $updatedAt->toDateTimeString();
        $isDraft      = ($requested === 'draft' || $status === 'draft');

        // Only resolve correction requests and sync into snapshot if table was completed (NOT a draft)
        if (!$isDraft && $status === 'done') {
            $candidates = array_unique([$tableNo, strtoupper($tableNo), strtolower($tableNo), preg_replace('/[^A-Za-z0-9]/', '', $tableNo)]);
            $openCorr = \App\Models\CorrectionRequest::whereIn('cmi_user_id', $allIds)
                ->where('reporting_year', $reportingYear)
                ->whereIn('table_no', $candidates)
                ->where('status', 'open')
                ->first();

            if ($openCorr) {
                $openCorr->update(['status' => 'resolved', 'resolved_at' => $updatedAt]);

                // Notify PTA admin that CMI has revised/fixed this table
                $cmiUser = Auth::user() ?? User::find($userId);
                $inst    = $cmiUser?->institution ?: ($cmiUser?->name ?? 'CMI User');
                \App\Models\Notification::create([
                    'role'         => 'pta',
                    'type'         => 'correction_fixed',
                    'icon'         => '📝',
                    'color'        => 'blue',
                    'message'      => "{$inst} revised and fixed Table {$tableNo} (CY {$reportingYear}).",
                    'action_url'   => "/dashboard/pta/submissions?year={$reportingYear}",
                    'action_label' => "Review {$tableNo}",
                    'is_read'      => false,
                    'created_at'   => now(),
                ]);
            }

            // Sync into latest submitted snapshot if this table was already in that snapshot
            $latestSub = ReportSubmission::whereIn('user_id', $allIds)
                ->where('reporting_year', $reportingYear)
                ->orderByDesc('id')
                ->first();

            if ($latestSub) {
                $snap = $latestSub->snapshot_json ?? [];
                if (is_array($snap)) {
                    $existingKey = null;
                    foreach ([$tableNo, strtoupper($tableNo), strtolower($tableNo)] as $candidate) {
                        if (array_key_exists($candidate, $snap)) {
                            $existingKey = $candidate;
                            break;
                        }
                    }
                    if ($existingKey && in_array($snap[$existingKey]['status'] ?? '', ['submitted', 'accepted', 'returned'], true)) {
                        $snap[$existingKey]['meta'] = $meta;
                        $snap[$existingKey]['rows'] = $rows;
                        $snap[$existingKey]['updated_at'] = $updatedAtStr;
                        // If table was returned, update its snapshot status to submitted so PTA sees it as resubmitted
                        if (($snap[$existingKey]['status'] ?? '') === 'returned' || $wasReturned) {
                            $snap[$existingKey]['status'] = 'submitted';
                            if (is_array($snap[$existingKey]['meta'])) {
                                $snap[$existingKey]['meta']['resubmitted_at'] = $updatedAtStr;
                            }
                        }
                        $latestSub->update(['snapshot_json' => $snap]);
                    }
                }
            }
        }

        // Send notifications based on who performed the update — strictly NEVER on drafts!
        if (!$isDraft) {
            try {
                $authId      = Auth::id() ?? session('user_id');
                $currentUser = $authId ? \App\Models\User::find($authId) : null;
                $isPtaEditor = ($currentUser && $currentUser->role === 'pta') || (session('user_role') === 'pta');

                if ($isPtaEditor) {
                    // PTA edited / filled up the table on behalf of CMI -> Notify CMI user(s)
                    $targetUser = \App\Models\User::find($userId);
                    $ptaName    = $currentUser?->name ?? 'PTA Admin';

                    $cmiUsers = \App\Models\User::where('role', 'cmi')
                        ->where(function ($q) use ($targetUser, $userId) {
                            if ($targetUser && !empty(trim((string) $targetUser->institution))) {
                                $q->where('institution', $targetUser->institution);
                            } else {
                                $q->where('id', $userId);
                            }
                        })
                        ->get();

                    foreach ($cmiUsers as $cmi) {
                        \App\Models\Notification::create([
                            'user_id'      => $cmi->id,
                            'role'         => 'cmi',
                            'type'         => 'table_edited',
                            'icon'         => '✏️',
                            'color'        => 'blue',
                            'message'      => "Table {$tableNo} (CY {$reportingYear}) was edited / updated by PTA ({$ptaName}).",
                            'action_url'   => "/dashboard/cmi/fillup?table={$tableNo}&year={$reportingYear}",
                            'action_label' => "View {$tableNo}",
                            'is_read'      => false,
                            'created_at'   => now(),
                        ]);
                    }
                }
                // Note: CMI saving or drafting a table does not notify PTA. PTA is only notified when CMI submits the report.
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to send notification on table update: ' . $e->getMessage());
            }
        }

        return response()->json([
            'success'    => true,
            'status'     => $status,
            'updated_at' => $updatedAtStr,
        ]);
    }

    public function statuses(Request $request): JsonResponse
    {
        $cmiParam      = $request->input('cmi_user_id');
        $userId        = $cmiParam ? (int) $cmiParam : (Auth::id() ?? session('user_id'));
        $reportingYear = (int) ($request->input('year') ?? date('Y'));

        $mateIds = $this->getInstMateIds($userId);
        $allIds  = array_merge([$userId], $mateIds);

        // Lock if there is an active submission for this user/institution
        $ownSubmission = ReportSubmission::whereIn('user_id', $allIds)
            ->where('reporting_year', $reportingYear)
            ->whereIn('status', ['pending', 'submitted', 'in-progress'])
            ->orderByDesc('submitted_at')
            ->first();

        $submittedAt = $ownSubmission?->submitted_at ? $ownSubmission->submitted_at->toDateTimeString() : null;

        if ($submittedAt) {
            $dt = new DateTime($submittedAt, new DateTimeZone('Asia/Manila'));
            $submittedAt = $dt->format('Y-m-d\TH:i:s') . '+08:00';
        }

        $submittedTables = [];
        if ($ownSubmission && !empty($ownSubmission->snapshot_json)) {
            $snap = $ownSubmission->snapshot_json;
            if (is_array($snap)) {
                $unlockedTables = ReportTable::whereIn('user_id', $allIds)
                    ->where('reporting_year', $reportingYear)
                    ->whereIn('status', ['returned', 'draft', 'not-started', 'deleted'])
                    ->pluck('table_no')
                    ->map(fn($t) => strtoupper($t))
                    ->toArray();

                foreach ($snap as $no => $data) {
                    if (in_array(strtoupper($no), $unlockedTables, true)) {
                        continue;
                    }
                    if (isset($data['status']) && in_array($data['status'], ['done', 'submitted', 'accepted'], true)) {
                        $submittedTables[] = $no;
                    }
                }
            }
        }

        $isSubmitted = (bool) $ownSubmission && !empty($submittedTables);

        // Build statuses from this user's records, merged with institution-mate records.
        // Higher-priority status wins: accepted > done/submitted > returned > draft > not-started.
        $priority = ['accepted' => 4, 'done' => 3, 'submitted' => 3, 'returned' => 2.5, 'draft' => 2, 'not-started' => 0, 'deleted' => 0];

        $canonicalMap = [];
        foreach (config('secreco.all_tables', []) as $stTable) {
            $canonicalMap[strtoupper($stTable)] = $stTable;
        }

        // Initialize statuses from latest ReportSubmission snapshot if available
        $statuses = [];
        $latestAnySub = ReportSubmission::whereIn('user_id', $allIds)
            ->where('reporting_year', $reportingYear)
            ->orderByDesc('id')
            ->first();

        if ($latestAnySub && is_array($latestAnySub->snapshot_json)) {
            foreach ($latestAnySub->snapshot_json as $snapNo => $snapItem) {
                if (is_array($snapItem) && !empty($snapItem['status']) && in_array($snapItem['status'], ['accepted', 'returned', 'submitted', 'done'], true)) {
                    $canon = $canonicalMap[strtoupper($snapNo)] ?? $snapNo;
                    $st = $snapItem['status'];
                    $statuses[$canon] = $st;
                    $statuses[strtoupper($canon)] = $st;
                    $statuses[strtolower($canon)] = $st;
                }
            }
        }

        $allRows = ReportTable::whereIn('user_id', $allIds)
            ->where('reporting_year', $reportingYear)
            ->get();

        foreach ($allRows as $r) {
            $st = $r->status;
            if ($st === 'deleted') {
                $st = 'not-started';
            } elseif ($st !== 'returned' && !$this->hasContent($r->meta_json, $r->rows_json)) {
                $st = 'not-started';
            }
            $canon = $canonicalMap[strtoupper($r->table_no)] ?? $r->table_no;
            $cur = $statuses[$canon] ?? 'not-started';
            if (($priority[$st] ?? 0) > ($priority[$cur] ?? 0)) {
                $statuses[$canon] = $st;
                $statuses[strtoupper($canon)] = $st;
                $statuses[strtolower($canon)] = $st;
            }
        }

        // Query open remarks from CorrectionRequest and enforce 'returned' status for tables flagged for correction
        // ONLY if the table has not already been fixed (i.e. status is not done, submitted, or accepted)
        $remarksMap = [];
        $openCorrections = \App\Models\CorrectionRequest::whereIn('cmi_user_id', $allIds)
            ->where('reporting_year', $reportingYear)
            ->where('status', 'open')
            ->orderByDesc('id')
            ->get();

        foreach ($openCorrections as $oc) {
            $canon = $canonicalMap[strtoupper($oc->table_no)] ?? $oc->table_no;
            $curSt = $statuses[$canon] ?? 'not-started';

            if (in_array($curSt, ['done', 'submitted', 'accepted'], true)) {
                // Table has already been fixed! Auto-resolve this correction request
                $oc->update(['status' => 'resolved', 'resolved_at' => now()]);
                continue;
            }

            $statuses[$canon] = 'returned';
            $statuses[strtoupper($canon)] = 'returned';
            $statuses[strtolower($canon)] = 'returned';

            if (!isset($remarksMap[$canon])) {
                $remarksMap[$canon] = $oc->reason;
                $remarksMap[strtoupper($canon)] = $oc->reason;
                $remarksMap[strtolower($canon)] = $oc->reason;
            }
        }

        if ($latestAnySub && $latestAnySub->status === 'returned' && !empty($latestAnySub->remarks)) {
            $remarksMap['_submission'] = $latestAnySub->remarks;
        }

        $templates = FormatTemplate::where('year', $reportingYear)
            ->orderBy('sort_order', 'asc')
            ->orderBy('table_no', 'asc')
            ->get(['id', 'year', 'table_no', 'title', 'section', 'is_required', 'is_locked', 'columns_json', 'sort_order', 'status']);

        return response()->json([
            'statuses'         => $statuses,
            'submitted'        => $isSubmitted,
            'submitted_at'     => $submittedAt,
            'submitted_tables' => $submittedTables,
            'templates'        => $templates,
            'remarks'          => $remarksMap,
        ]);
    }

    public function uploadDoc(Request $request): JsonResponse
    {
        $cmiParam = $request->input('cmi_user_id');
        $userId   = $cmiParam ? (int) $cmiParam : (Auth::id() ?? session('user_id'));

        // Case 1: Caption update call (doc_id + caption)
        if ($request->has('doc_id') && $request->has('caption')) {
            $docId   = (int) $request->input('doc_id');
            $caption = trim($request->input('caption', ''));
            ReportTableDoc::where('id', $docId)->where('user_id', $userId)->update(['caption' => $caption]);
            return response()->json(['success' => true]);
        }

        // Case 2: File upload
        $files = [];
        if ($request->hasFile('images')) {
            $uploaded = $request->file('images');
            $files = is_array($uploaded) ? $uploaded : [$uploaded];
        } elseif ($request->hasFile('file')) {
            $files = [$request->file('file')];
        }

        if (empty($files)) {
            return response()->json(['success' => false, 'error' => 'No files uploaded.']);
        }

        $rawTable = $request->input('table_no', '');
        $tableKey = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $rawTable));
        $tableDb  = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $rawTable));
        if (str_starts_with($tableDb, 'TABLE') && strlen($tableDb) > 5) {
            $tableDb = substr($tableDb, 5);
        }

        if ($tableKey === '') {
            return response()->json(['success' => false, 'error' => 'Missing table_no']);
        }

        $year = (int) ($request->input('year') ?? date('Y'));

        $maxSort = ReportTableDoc::where('user_id', $userId)
            ->where('reporting_year', $year)
            ->whereIn('table_no', [$tableDb, $rawTable, strtoupper($rawTable)])
            ->max('sort_order');
        $sortOrder = ($maxSort !== null ? $maxSort : -1) + 1;

        $createdFiles = [];
        foreach ($files as $file) {
            if (!$file->isValid()) continue;

            if ($file->getSize() > 5 * 1024 * 1024) {
                return response()->json([
                    'success' => false,
                    'error'   => 'File size exceeds the 5MB limit. Please upload a file smaller than 5MB.'
                ], 422);
            }

            $ext      = strtolower($file->getClientOriginalExtension());
            $pfx      = ($ext === 'pdf') ? 'doc_' : 'img_';
            $filename = $pfx . bin2hex(random_bytes(8)) . '.' . time() . '.' . $ext;
            $path     = $file->storeAs("uploads/cmi/{$tableKey}", $filename, 'public');
            $webpath  = "/storage/uploads/cmi/{$tableKey}/{$filename}";

            $doc = ReportTableDoc::create([
                'user_id'        => $userId,
                'reporting_year' => $year,
                'table_no'       => $tableDb,
                'file_path'      => $webpath,
                'caption'        => '',
                'sort_order'     => $sortOrder++,
                'uploaded_at'    => now(),
            ]);

            $createdFiles[] = [
                'id'        => (int) $doc->id,
                'doc_id'    => (int) $doc->id,
                'file_path' => $webpath,
                'caption'   => '',
            ];
        }

        return response()->json([
            'success'   => true,
            'files'     => $createdFiles,
            'doc_id'    => $createdFiles[0]['id'] ?? 0,
            'file_path' => $createdFiles[0]['file_path'] ?? '',
        ]);
    }

    public function deleteDoc(Request $request): JsonResponse
    {
        $body     = $request->json()->all() ?: $request->all();
        $docId    = (int) ($body['doc_id'] ?? $body['id'] ?? $request->input('doc_id') ?? $request->input('id') ?? 0);
        $cmiParam = $body['cmi_user_id'] ?? $request->input('cmi_user_id');
        $userId   = $cmiParam ? (int) $cmiParam : (Auth::id() ?? session('user_id'));

        if ($docId <= 0) {
            return response()->json(['success' => false, 'error' => 'Invalid doc_id']);
        }

        $doc = ReportTableDoc::where('id', $docId)->first();
        if ($doc) {
            $doc->delete();
        }

        return response()->json(['success' => true]);
    }

    protected function hasContent($meta, $rows): bool
    {
        if (is_array($rows)) {
            foreach ($rows as $row) {
                if (!is_array($row)) {
                    if (trim((string) $row) !== '') return true;
                    continue;
                }
                foreach ($row as $val) {
                    if (is_array($val)) {
                        foreach ($val as $v) {
                            if (trim((string) $v) !== '') return true;
                        }
                    } elseif (trim((string) $val) !== '') {
                        return true;
                    }
                }
            }
        }

        $metaArr = (array) $meta;
        foreach ($metaArr as $val) {
            if (is_array($val)) {
                foreach ($val as $v) {
                    if (trim((string) $v) !== '') return true;
                }
            } elseif (trim((string) $val) !== '') {
                return true;
            }
        }

        return false;
    }

    /**
     * Return IDs of all CMI users that share the same institution as $userId
     * (excluding $userId itself). Returns an empty array if no institution is set.
     */
    protected function getInstMateIds(int $userId): array
    {
        $me = User::find($userId);
        if (!$me || empty(trim((string) $me->institution))) {
            return [];
        }
        return User::where('institution', $me->institution)
            ->where('id', '!=', $userId)
            ->where('role', '!=', 'pta')
            ->pluck('id')
            ->toArray();
    }
}
