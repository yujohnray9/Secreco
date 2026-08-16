<?php

namespace App\Http\Controllers\Pta;

use App\Http\Controllers\Controller;
use App\Models\ReportSubmission;
use App\Models\ReportTable;
use App\Models\ReportTableDoc;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function getConsolidated(Request $request): JsonResponse
    {
        $year       = (int) ($request->input('year') ?? date('Y'));
        $table      = trim($request->input('table', 'T1'));
        $tableUpper = strtoupper($table);

        // ── Step 1: Collect ACCEPTED report submissions for this year ──────
        $latestSubIds = ReportSubmission::where('reporting_year', $year)
            ->where('status', 'accepted')
            ->selectRaw('MAX(id) as max_id')
            ->groupBy('user_id')
            ->pluck('max_id');

        $subRows = ReportSubmission::whereIn('id', $latestSubIds)
            ->whereHas('user', function ($q) {
                $q->where('role', 'cmi');
            })
            ->with('user')
            ->get();

        // Build a map: user_id => result entry (from accepted submissions)
        $resultMap = [];
        foreach ($subRows as $row) {
            $snap  = $row->snapshot_json ?? [];
            $uid   = $row->user_id;
            $tData = $snap[$table] ?? $snap[$tableUpper] ?? $snap[strtolower($table)] ?? null;
            $tStatus = (is_array($tData) && isset($tData['status']) && $tData['status'] === 'accepted') ? 'accepted' : 'not-started';

            $resultMap[$uid] = [
                'user_id'      => $uid,
                'institution'  => $row->user?->institution ?: $row->user?->name,
                'submitted_at' => $row->submitted_at ? $row->submitted_at->toDateTimeString() : null,
                'sub_status'   => 'accepted',
                'table_status' => $tStatus,
                'updated_at'   => is_array($tData) ? ($tData['updated_at'] ?? null) : null,
                'rows'         => is_array($tData) ? ($tData['rows'] ?? []) : [],
                'meta'         => is_array($tData) ? ($tData['meta'] ?? null) : null,
                'docs'         => [],
            ];
        }

        // ── Step 2: Read ACCEPTED report_tables directly ──
        $directRows = ReportTable::where('reporting_year', $year)
            ->where('table_no', $tableUpper)
            ->where('status', 'accepted')
            ->whereHas('user', function ($q) {
                $q->where('role', 'cmi');
            })
            ->with('user')
            ->get();

        $statusPriority = ['accepted' => 4, 'done' => 4, 'submitted' => 4, 'returned' => 3, 'draft' => 2, 'not-started' => 0];

        foreach ($directRows as $rt) {
            $uid = $rt->user_id;
            $st  = 'accepted';

            if (isset($resultMap[$uid])) {
                $resultMap[$uid]['table_status'] = 'accepted';
                $resultMap[$uid]['updated_at']   = $rt->updated_at ? $rt->updated_at->toDateTimeString() : null;
                if (!empty($rt->rows_json)) {
                    $resultMap[$uid]['rows'] = $rt->rows_json;
                }
                if (!empty($rt->meta_json)) {
                    $resultMap[$uid]['meta'] = $rt->meta_json;
                }
            } else {
                $resultMap[$uid] = [
                    'user_id'      => $uid,
                    'institution'  => $rt->user?->institution ?: $rt->user?->name,
                    'submitted_at' => null,
                    'sub_status'   => 'accepted',
                    'table_status' => 'accepted',
                    'updated_at'   => $rt->updated_at ? $rt->updated_at->toDateTimeString() : null,
                    'rows'         => $rt->rows_json ?? [],
                    'meta'         => $rt->meta_json ?? null,
                    'docs'         => [],
                ];
            }
        }

        // ── Step 3: Also add ALL CMI users with no data yet (for Per CMI view) ──
        $allCmiUsers = User::where('role', 'cmi')
            ->select('id', 'institution', 'first_name', 'last_name')
            ->get();

        foreach ($allCmiUsers as $u) {
            if (!isset($resultMap[$u->id])) {
                $resultMap[$u->id] = [
                    'user_id'      => $u->id,
                    'institution'  => $u->institution ?: $u->name,
                    'submitted_at' => null,
                    'sub_status'   => 'not-submitted',
                    'table_status' => 'not-started',
                    'updated_at'   => null,
                    'rows'         => [],
                    'meta'         => null,
                    'docs'         => [],
                ];
            }
        }

        // ── Step 4: Attach uploaded docs ─────────────────────────────────────────
        $allDocs = ReportTableDoc::where('reporting_year', $year)
            ->where('table_no', $tableUpper)
            ->orderBy('sort_order', 'asc')
            ->orderBy('uploaded_at', 'asc')
            ->get()
            ->groupBy('user_id');

        foreach ($resultMap as $uid => &$entry) {
            if (isset($allDocs[$uid])) {
                $entry['docs'] = $allDocs[$uid]->toArray();
            }
            if (($entry['table_status'] ?? '') !== 'accepted') {
                $entry['rows'] = [];
            }
        }
        unset($entry);

        // ── Step 5: De-duplicate by institution (pick best status per institution) ─
        $byInstitution = [];
        foreach ($resultMap as $entry) {
            $inst = $entry['institution'] ?? 'Unknown';
            if (!isset($byInstitution[$inst])) {
                $byInstitution[$inst] = $entry;
            } else {
                $cur = $statusPriority[$byInstitution[$inst]['table_status']] ?? 0;
                $new = $statusPriority[$entry['table_status']] ?? 0;
                if ($new > $cur) {
                    $byInstitution[$inst] = $entry;
                }
            }
        }

        $result = array_values($byInstitution);

        // Sort: institutions with data first, then alphabetically
        usort($result, function ($a, $b) use ($statusPriority) {
            $pa = $statusPriority[$a['table_status']] ?? 0;
            $pb = $statusPriority[$b['table_status']] ?? 0;
            if ($pa !== $pb) return $pb - $pa;
            return strcmp($a['institution'] ?? '', $b['institution'] ?? '');
        });

        return response()->json(['ok' => true, 'year' => $year, 'table' => $table, 'data' => $result]);
    }
}
