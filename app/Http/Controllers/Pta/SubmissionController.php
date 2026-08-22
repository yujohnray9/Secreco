<?php

namespace App\Http\Controllers\Pta;

use App\Http\Controllers\Controller;
use App\Models\CorrectionRequest;
use App\Models\Notification;
use App\Models\ReportSubmission;
use App\Models\ReportTable;
use App\Models\ReportTableDoc;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubmissionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $year   = (int) ($request->input('year') ?? date('Y'));
        $status = $request->input('status');

        $cmiUsers = User::where('role', 'cmi')
            ->whereIn('status', ['active', 'approved', 'pending'])
            ->get();

        $formatTables = \App\Models\FormatTemplate::where('year', $year)
            ->orderBy('sort_order', 'asc')
            ->pluck('table_no')
            ->toArray();

        if (empty($formatTables)) {
            $formatTables = ['T1','T2a','T2b','T3','T4','T5','T6','T7a','T7b','T8a','T8b','T9','T10','T11','T12','T13','T14','T15','T16','T17','T18','T19','T20a','T20b'];
        }

        $tableRecords = ReportTable::where('reporting_year', $year)
            ->whereIn('user_id', $cmiUsers->pluck('id'))
            ->with('user')
            ->get();

        $tableMap = [];
        foreach ($tableRecords as $tr) {
            $key = $tr->user_id . '_' . $tr->table_no;
            $tableMap[$key] = $tr;
        }

        $userIds = $cmiUsers->pluck('id')->toArray();

        $submissionDates = [];
        if ($userIds) {
            $subs = ReportSubmission::where('reporting_year', $year)
                ->whereIn('user_id', $userIds)
                ->orderBy('submitted_at', 'desc')
                ->get(['user_id', 'submitted_at']);
            foreach ($subs as $sub) {
                if (!isset($submissionDates[$sub->user_id])) {
                    $submissionDates[$sub->user_id] = $sub->submitted_at ? (is_string($sub->submitted_at) ? $sub->submitted_at : $sub->submitted_at->toDateTimeString()) : null;
                }
            }
        }

        $docsByUserTable = [];
        if ($userIds) {
            $docs = ReportTableDoc::where('reporting_year', $year)
                ->whereIn('user_id', $userIds)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            foreach ($docs as $doc) {
                $cleanTable = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $doc->table_no));
                if (str_starts_with($cleanTable, 'TABLE') && strlen($cleanTable) > 5) {
                    $cleanTable = substr($cleanTable, 5);
                }
                $item = [
                    'id'        => (int) $doc->id,
                    'doc_id'    => (int) $doc->id,
                    'file_path' => $doc->file_path,
                    'caption'   => $doc->caption ?? '',
                ];
                $docsByUserTable[$doc->user_id . '_' . $doc->table_no][] = $item;
                if ($cleanTable && $cleanTable !== $doc->table_no) {
                    $docsByUserTable[$doc->user_id . '_' . $cleanTable][] = $item;
                }
            }
        }

        $corrByUserTable = [];
        if ($userIds) {
            $corrections = CorrectionRequest::where('reporting_year', $year)
                ->where('status', 'open')
                ->whereIn('cmi_user_id', $userIds)
                ->get();

            foreach ($corrections as $cr) {
                $key = $cr->cmi_user_id . '_' . $cr->table_no;
                $corrByUserTable[$key] = true;
            }
        }

        $groupedUsers = $cmiUsers->groupBy(function ($u) {
            return trim($u->institution) ?: ($u->name ?? 'CMI User');
        });

        $rows = [];
        foreach ($groupedUsers as $instName => $instUsers) {
            $primaryUser = $instUsers->first();

            foreach ($formatTables as $tNo) {
                $foundTr = null;
                $foundUser = $primaryUser;

                $statusPriority = ['accepted' => 4, 'done' => 4, 'submitted' => 4, 'returned' => 3, 'draft' => 2, 'not-started' => 0];

                foreach ($instUsers as $u) {
                    $key = $u->id . '_' . $tNo;
                    if (isset($tableMap[$key])) {
                        $tr = $tableMap[$key];
                        if (!$foundTr) {
                            $foundTr = $tr;
                            $foundUser = $u;
                        } else {
                            $pCurrent = $statusPriority[$tr->status] ?? 0;
                            $pBest    = $statusPriority[$foundTr->status] ?? 0;

                            if ($pCurrent > $pBest) {
                                $foundTr = $tr;
                                $foundUser = $u;
                            } elseif ($pCurrent === $pBest) {
                                $tCurrent = $tr->updated_at ? strtotime((string)$tr->updated_at) : 0;
                                $tBest    = $foundTr->updated_at ? strtotime((string)$foundTr->updated_at) : 0;
                                if ($tCurrent > $tBest) {
                                    $foundTr = $tr;
                                    $foundUser = $u;
                                }
                            }
                        }
                    }
                }

                $st = $foundTr ? $foundTr->status : 'not-started';
                if ($st === 'draft' && !$foundTr->meta_json && empty($foundTr->rows_json)) {
                    $st = 'not-started';
                }

                if ($status && $status !== '' && $st !== $status) {
                    continue;
                }

                // Exclude not-started tables by default unless explicitly filtered
                if ($st === 'not-started' && $status !== 'not-started') {
                    continue;
                }

                $key = $foundUser->id . '_' . $tNo;
                $submittedAt = ($foundTr?->updated_at ? $foundTr->updated_at->toDateTimeString() : null) ?? ($submissionDates[$foundUser->id] ?? ($foundTr?->created_at ? $foundTr->created_at->toDateTimeString() : null));

                $rows[] = [
                    'cmi_user_id'         => (int) $foundUser->id,
                    'institution'         => $instName,
                    'encoder'             => $foundUser->name,
                    'table_no'            => $tNo,
                    'table_status'        => $st,
                    'submitted_at'        => $submittedAt,
                    'updated_at'          => $foundTr?->updated_at ? $foundTr->updated_at->toDateTimeString() : null,
                    'meta'                => $foundTr?->meta_json ?? [],
                    'rows'                => $foundTr?->rows_json ?? [],
                    'docs'                => $docsByUserTable[$key] ?? [],
                    'has_open_correction' => isset($corrByUserTable[$key]),
                ];
            }
        }

        usort($rows, function ($a, $b) {
            $cmp = strcmp($a['institution'], $b['institution']);
            if ($cmp !== 0) return $cmp;
            return strcmp($a['table_no'], $b['table_no']);
        });

        return response()->json(['ok' => true, 'year' => $year, 'rows' => $rows]);
    }

    public function accept(Request $request): JsonResponse
    {
        $submissionId = (int) $request->input('submission_id', 0);
        $id           = (int) $request->input('id', 0);
        $cmiUserId    = (int) $request->input('cmi_user_id', 0);
        $tableNo      = strtoupper(trim($request->input('table_no', '')));
        $year         = (int) ($request->input('year') ?? date('Y'));

        $sub = null;
        if ($submissionId > 0 || $id > 0) {
            $sub = ReportSubmission::find($submissionId ?: $id);
        }

        if (!$sub && $cmiUserId > 0) {
            $sub = ReportSubmission::where('user_id', $cmiUserId)
                ->where('reporting_year', $year)
                ->orderByDesc('id')
                ->first();
        }

        if ($sub && !$cmiUserId) {
            $cmiUserId = (int) $sub->user_id;
        }

        if ($cmiUserId > 0) {
            $q = ReportTable::where('user_id', $cmiUserId)->where('reporting_year', $year);
            if ($tableNo !== '') {
                $q->where('table_no', $tableNo);
            }
            $q->update(['status' => 'accepted', 'updated_at' => now()]);
        }

        if ($sub) {
            $snap = $sub->snapshot_json ?? [];
            if (is_array($snap)) {
                if ($tableNo !== '' && isset($snap[$tableNo])) {
                    $snap[$tableNo]['status'] = 'accepted';
                } else {
                    foreach ($snap as $k => $v) {
                        if (is_array($v)) {
                            $snap[$k]['status'] = 'accepted';
                        }
                    }
                }
            }
            $sub->update(['status' => 'accepted', 'remarks' => null, 'snapshot_json' => $snap]);
        }

        $cmiUser = User::find($cmiUserId);
        $inst = $cmiUser?->institution ?: ($cmiUser?->name ?? 'CMI User');

        if ($cmiUser) {
            Notification::create([
                'user_id'      => $cmiUser->id,
                'role'         => 'cmi',
                'type'         => 'submitted',
                'icon'         => '✅',
                'color'        => 'green',
                'message'      => "Your CY {$year} Table {$tableNo} submission has been accepted by PTA. Great work!",
                'action_url'   => '/dashboard/cmi/submissions',
                'action_label' => 'View',
                'is_read'      => false,
                'created_at'   => now(),
            ]);
        }

        $ptaId = Auth::id() ?? session('user_id');
        ActivityLogService::log($ptaId, "Accepted submission Table {$tableNo} from {$inst} (CY {$year})");

        return response()->json(['ok' => true, 'message' => "Table {$tableNo} submission from {$inst} accepted successfully."]);
    }

    public function requestCorrection(Request $request): JsonResponse
    {
        $cmiUserId = (int) $request->input('cmi_user_id', 0);
        $tableNo   = strtoupper(trim($request->input('table_no', '')));
        $year      = (int) ($request->input('year') ?? date('Y'));
        $reason    = trim($request->input('reason', ''));
        $ptaUserId = (int) (Auth::id() ?? session('user_id'));

        if (!$cmiUserId || !$tableNo || !$reason) {
            return response()->json(['ok' => false, 'error' => 'Missing required fields.']);
        }
        if (mb_strlen($reason) > 2000) {
            return response()->json(['ok' => false, 'error' => 'Reason is too long (max 2000 characters).']);
        }

        $check = User::where('id', $cmiUserId)->where('role', 'cmi')->where('status', 'active')->exists();
        if (!$check) {
            return response()->json(['ok' => false, 'error' => 'CMI user not found.']);
        }

        ReportTable::where('user_id', $cmiUserId)
            ->where('reporting_year', $year)
            ->where('table_no', $tableNo)
            ->update(['status' => 'returned', 'updated_at' => now()]);

        CorrectionRequest::create([
            'cmi_user_id'    => $cmiUserId,
            'pta_user_id'    => $ptaUserId,
            'reporting_year' => $year,
            'table_no'       => $tableNo,
            'reason'         => $reason,
            'status'         => 'open',
            'created_at'     => now(),
        ]);

        $ptaUser  = User::find($ptaUserId);
        $ptaName  = $ptaUser?->name ?? 'PTA';
        $notifMsg = "{$tableNo} has been flagged for correction by {$ptaName}: \"{$reason}\"";

        Notification::create([
            'user_id'      => $cmiUserId,
            'role'         => 'cmi',
            'type'         => 'correction',
            'icon'         => '🔴',
            'color'        => 'red',
            'message'      => $notifMsg,
            'action_url'   => "/dashboard/cmi/fillup?table={$tableNo}&year={$year}",
            'action_label' => "Fix {$tableNo}",
            'is_read'      => false,
            'created_at'   => now(),
        ]);

        ReportSubmission::where('user_id', $cmiUserId)
            ->where('reporting_year', $year)
            ->whereIn('status', ['pending', 'in-progress', 'submitted'])
            ->orderByDesc('submitted_at')
            ->first()
            ?->update(['status' => 'returned', 'remarks' => $reason]);

        ActivityLogService::log($ptaUserId, "Correction requested for {$tableNo} — CMI user #{$cmiUserId}: \"{$reason}\"");

        return response()->json(['ok' => true]);
    }

    public function delete(Request $request): JsonResponse
    {
        $cmiUserId = (int) $request->input('cmi_user_id', 0);
        $tableNo   = strtoupper(trim($request->input('table_no', '')));
        $year      = (int) ($request->input('year') ?? date('Y'));
        $ptaUserId = (int) (Auth::id() ?? session('user_id'));

        if (!$cmiUserId || !$tableNo) {
            return response()->json(['ok' => false, 'error' => 'Missing required fields.']);
        }

        $cmiUser = User::find($cmiUserId);
        $inst    = $cmiUser?->institution ?: ($cmiUser?->name ?? 'CMI User');
        $allIds  = [$cmiUserId];
        if ($cmiUser && !empty(trim((string)$cmiUser->institution))) {
            $mateIds = User::where('institution', $cmiUser->institution)
                ->where('role', '!=', 'pta')
                ->pluck('id')
                ->toArray();
            $allIds = array_values(array_unique(array_merge($allIds, $mateIds)));
        }

        ReportTable::whereIn('user_id', $allIds)
            ->where('reporting_year', $year)
            ->where('table_no', $tableNo)
            ->update([
                'status'     => 'deleted',
                'rows_json'  => [],
                'meta_json'  => [],
                'updated_at' => now(),
            ]);

        $subs = ReportSubmission::whereIn('user_id', $allIds)
            ->where('reporting_year', $year)
            ->get();

        foreach ($subs as $sub) {
            $snap = $sub->snapshot_json ?? [];
            if (is_array($snap)) {
                $candidates = [$tableNo, strtolower($tableNo), strtoupper($tableNo), 'TABLE ' . $tableNo, 'Table ' . $tableNo];
                foreach ($candidates as $k) {
                    if (isset($snap[$k])) {
                        if (is_array($snap[$k])) {
                            $snap[$k]['status'] = 'deleted';
                        }
                    }
                }

                $hasSubmittedTable = false;
                foreach ($snap as $tKey => $tVal) {
                    $st = is_array($tVal) ? ($tVal['status'] ?? '') : '';
                    if (in_array($st, ['done', 'submitted', 'accepted'], true)) {
                        $hasSubmittedTable = true;
                        break;
                    }
                }

                $updateData = ['snapshot_json' => $snap];
                if (!$hasSubmittedTable) {
                    $updateData['status'] = 'deleted';
                }
                $sub->update($updateData);
            }
        }

        Notification::create([
            'user_id'      => $cmiUserId,
            'role'         => 'cmi',
            'type'         => 'deleted',
            'icon'         => '🗑️',
            'color'        => 'red',
            'message'      => "Your CY {$year} Table {$tableNo} submission was deleted by PTA. You may now edit and re-submit it.",
            'action_url'   => '/dashboard/cmi/fillup',
            'action_label' => "Fill Up {$tableNo}",
            'is_read'      => false,
            'created_at'   => now(),
        ]);

        ActivityLogService::log($ptaUserId, "Deleted submission {$tableNo} from {$inst} (CY {$year})");

        return response()->json(['ok' => true, 'message' => "Submission {$tableNo} from {$inst} deleted."]);
    }

    public function updateTable(Request $request): JsonResponse
    {
        $cmiUserId = (int) $request->input('cmi_user_id', 0);
        $tableNo   = strtoupper(trim($request->input('table_no', '')));
        $year      = (int) ($request->input('year') ?? date('Y'));
        $rows      = $request->input('rows', []);
        $meta      = $request->input('meta', []);
        $status    = $request->input('status', 'done');

        if (!$cmiUserId || !$tableNo) {
            return response()->json(['ok' => false, 'error' => 'Missing required fields.']);
        }

        $table = ReportTable::updateOrCreate(
            ['user_id' => $cmiUserId, 'reporting_year' => $year, 'table_no' => $tableNo],
            [
                'meta_json'  => $meta,
                'rows_json'  => $rows,
                'status'     => $status,
                'updated_at' => now(),
            ]
        );

        $latestSub = ReportSubmission::where('user_id', $cmiUserId)
            ->where('reporting_year', $year)
            ->orderByDesc('id')
            ->first();

        if (!$latestSub) {
            $latestSub = ReportSubmission::create([
                'user_id'        => $cmiUserId,
                'reporting_year' => $year,
                'status'         => 'pending',
                'snapshot_json'  => [],
                'submitted_at'   => now(),
            ]);
        }

        $snap = $latestSub->snapshot_json ?? [];
        if (!is_array($snap)) $snap = [];
        $snap[$tableNo] = [
            'status'     => $status,
            'meta'       => $meta,
            'rows'       => $rows,
            'updated_at' => now()->toDateTimeString(),
        ];
        $latestSub->update(['snapshot_json' => $snap]);

        $ptaUserId = Auth::id() ?? session('user_id');
        $ptaUser   = $ptaUserId ? User::find($ptaUserId) : null;
        $ptaName   = $ptaUser?->name ?? 'PTA Admin';
        $cmiUser   = User::find($cmiUserId);
        $inst      = $cmiUser?->institution ?: ($cmiUser?->name ?? 'CMI User');

        // Notify CMI user(s) of this institution about the PTA edit
        try {
            $targetCmis = User::where('role', 'cmi')
                ->where(function ($q) use ($cmiUser, $cmiUserId) {
                    if ($cmiUser && !empty(trim((string) $cmiUser->institution))) {
                        $q->where('institution', $cmiUser->institution);
                    } else {
                        $q->where('id', $cmiUserId);
                    }
                })
                ->get();

            foreach ($targetCmis as $target) {
                Notification::create([
                    'user_id'      => $target->id,
                    'role'         => 'cmi',
                    'type'         => 'table_edited',
                    'icon'         => '✏️',
                    'color'        => 'blue',
                    'message'      => "Table {$tableNo} (CY {$year}) was edited / updated by PTA ({$ptaName}).",
                    'action_url'   => "/dashboard/cmi/fillup?table={$tableNo}&year={$year}",
                    'action_label' => "View {$tableNo}",
                    'is_read'      => false,
                    'created_at'   => now(),
                ]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to notify CMI on PTA table update: ' . $e->getMessage());
        }

        ActivityLogService::log($ptaUserId, "PTA updated Table {$tableNo} for {$inst} (CY {$year})");

        return response()->json(['ok' => true, 'message' => "Table {$tableNo} for {$inst} updated successfully."]);
    }
}
