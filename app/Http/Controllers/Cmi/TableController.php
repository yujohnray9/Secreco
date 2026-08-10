<?php

namespace App\Http\Controllers\Cmi;

use App\Http\Controllers\Controller;
use App\Models\CmiTableImage;
use App\Models\ReportSubmission;
use App\Models\ReportTable;
use App\Models\ReportTableDoc;
use DateTime;
use DateTimeZone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TableController extends Controller
{
    public function load(Request $request): JsonResponse
    {
        $userId        = Auth::id() ?? session('user_id');
        $reportingYear = (int) ($request->input('year') ?? date('Y'));
        $tableNo       = preg_replace('/[^A-Za-z0-9]/', '', $request->input('table_no', ''));

        if (!$tableNo) {
            return response()->json(['error' => 'Missing table_no']);
        }

        $row = ReportTable::where('user_id', $userId)
            ->where('reporting_year', $reportingYear)
            ->where('table_no', $tableNo)
            ->first();

        if (!$row) {
            return response()->json(['status' => 'not-started', 'meta' => null, 'rows' => [], 'updated_at' => null]);
        }

        $images = ReportTableDoc::where('user_id', $userId)
            ->where('reporting_year', $reportingYear)
            ->where('table_no', $tableNo)
            ->orderBy('sort_order', 'asc')
            ->orderBy('uploaded_at', 'asc')
            ->get(['id', 'file_path', 'caption'])
            ->map(function ($img) {
                if ($img->file_path) {
                    $img->file_path = '/' . ltrim($img->file_path, '/');
                }
                return $img;
            });

        $meta = $row->meta_json ?? [];
        if (is_array($meta)) {
            unset($meta['images']);
        }

        return response()->json([
            'status'     => $row->status,
            'meta'       => $meta,
            'rows'       => $row->rows_json ?? [],
            'updated_at' => $row->updated_at ? $row->updated_at->toDateTimeString() : null,
            'docs'       => $images,
        ]);
    }

    public function save(Request $request): JsonResponse
    {
        $body = $request->json()->all() ?: $request->all();
        if (!$body) {
            return response()->json(['error' => 'Invalid JSON']);
        }

        $userId        = Auth::id() ?? session('user_id');
        $reportingYear = (int) ($body['year'] ?? date('Y'));
        $tableNo       = preg_replace('/[^A-Za-z0-9]/', '', $body['table_no'] ?? '');
        $meta          = $body['meta'] ?? new \stdClass();
        $rows          = $body['rows'] ?? [];
        $requested     = in_array($body['status'] ?? '', ['not-started', 'draft', 'done', 'error'], true) ? $body['status'] : 'draft';

        if (!$tableNo) {
            return response()->json(['error' => 'Missing table_no']);
        }

        $status = $requested;
        if ($status === 'draft' && !$this->hasContent($meta, $rows)) {
            $status = 'not-started';
        }

        $updatedAt = now();

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

        // Sync into latest submitted snapshot if exists
        $latestSub = ReportSubmission::where('user_id', $userId)
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
                $key = $existingKey ?? $tableNo;

                $snap[$key] = [
                    'status'     => $status,
                    'meta'       => $meta,
                    'rows'       => $rows,
                    'updated_at' => $updatedAtStr,
                ];

                $latestSub->update(['snapshot_json' => $snap]);
            }
        }

        // Notify PTA admins about the table update
        try {
            $user = \App\Models\User::find($userId);
            $inst = $user?->institution ?: ($user?->name ?: 'CMI Representative');
            $ptaUsers = \App\Models\User::where('role', 'pta')->get();
            foreach ($ptaUsers as $ptaUser) {
                \App\Models\Notification::create([
                    'user_id'      => $ptaUser->id,
                    'role'         => 'pta',
                    'type'         => 'submission_updated',
                    'icon'         => 'edit',
                    'color'        => 'blue',
                    'message'      => "{$inst} updated Table {$tableNo}.",
                    'action_url'   => '/dashboard/pta/submissions',
                    'action_label' => 'View Submissions',
                    'is_read'      => false,
                    'created_at'   => now(),
                ]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to notify PTA on table update: ' . $e->getMessage());
        }

        return response()->json([
            'success'    => true,
            'status'     => $status,
            'updated_at' => $updatedAtStr,
        ]);
    }

    public function statuses(Request $request): JsonResponse
    {
        $userId        = Auth::id() ?? session('user_id');
        $reportingYear = (int) ($request->input('year') ?? date('Y'));

        $submission = ReportSubmission::where('user_id', $userId)
            ->where('reporting_year', $reportingYear)
            ->orderByDesc('submitted_at')
            ->first();

        $isSubmitted = (bool) $submission;
        $submittedAt = $submission?->submitted_at ? $submission->submitted_at->toDateTimeString() : null;

        if ($submittedAt) {
            $dt = new DateTime($submittedAt, new DateTimeZone('Asia/Manila'));
            $submittedAt = $dt->format('Y-m-d\TH:i:s') . '+08:00';
        }

        $submittedTables = [];
        if ($submission && !empty($submission->snapshot_json)) {
            $snap = $submission->snapshot_json;
            if (is_array($snap)) {
                foreach ($snap as $no => $data) {
                    if (isset($data['status']) && $data['status'] === 'done') {
                        $submittedTables[] = $no;
                    }
                }
            }
        }

        $rows = ReportTable::where('user_id', $userId)->where('reporting_year', $reportingYear)->get();

        $statuses = [];
        foreach ($rows as $r) {
            $st = $r->status;
            if ($st === 'draft' && !$this->hasContent($r->meta_json, $r->rows_json)) {
                $st = 'not-started';
            }
            $statuses[$r->table_no] = $st;
        }

        return response()->json([
            'statuses'         => $statuses,
            'submitted'        => $isSubmitted,
            'submitted_at'     => $submittedAt,
            'submitted_tables' => $submittedTables,
        ]);
    }

    public function uploadDoc(Request $request): JsonResponse
    {
        $userId = Auth::id() ?? session('user_id');

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
        $tableDb  = strtoupper($rawTable);

        if ($tableKey === '') {
            return response()->json(['success' => false, 'error' => 'Missing table_no']);
        }

        $year = (int) ($request->input('year') ?? date('Y'));

        $maxSort = ReportTableDoc::where('user_id', $userId)
            ->where('reporting_year', $year)
            ->where('table_no', $tableDb)
            ->max('sort_order');
        $sortOrder = ($maxSort !== null ? $maxSort : -1) + 1;

        $createdFiles = [];
        foreach ($files as $file) {
            if (!$file->isValid()) continue;

            $ext      = strtolower($file->getClientOriginalExtension());
            $filename = 'img_' . bin2hex(random_bytes(8)) . '.' . time() . '.' . $ext;
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
        $docId  = (int) ($request->input('doc_id') ?? $request->input('id') ?? 0);
        $userId = Auth::id() ?? session('user_id');

        if ($docId <= 0) {
            return response()->json(['success' => false, 'error' => 'Invalid doc_id']);
        }

        $doc = ReportTableDoc::where('id', $docId)->where('user_id', $userId)->first();
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
}
