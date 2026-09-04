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
        try {
            $year       = (int) ($request->input('year') ?? date('Y'));
            $table      = trim($request->input('table', 'T1'));
            $tableLower = strtolower($table);

            $statusPriority = ['accepted' => 5, 'done' => 4, 'submitted' => 3, 'draft' => 2, 'not-started' => 0];

            // ── Step 1: Collect all CMI users ────────────────────────────
            $allCmiUsers = User::where('role', 'cmi')
                ->select('id', 'institution', 'first_name', 'last_name')
                ->get();

            // ── Step 2: Collect latest report submissions for this year ──
            $latestSubIds = ReportSubmission::where('reporting_year', $year)
                ->selectRaw('MAX(id) as max_id')
                ->groupBy('user_id')
                ->pluck('max_id');

            $subRows = ReportSubmission::whereIn('id', $latestSubIds)
                ->whereHas('user', function ($q) {
                    $q->where('role', 'cmi');
                })
                ->with('user')
                ->get();

            if ($tableLower === 'all') {
                // Fetch all direct report_tables and docs in single queries
                $allDirectRows = ReportTable::where('reporting_year', $year)
                    ->whereNotIn('status', ['deleted'])
                    ->whereHas('user', function ($q) {
                        $q->where('role', 'cmi');
                    })
                    ->with('user')
                    ->get();

                $allDocs = ReportTableDoc::where('reporting_year', $year)
                    ->orderBy('sort_order', 'asc')
                    ->orderBy('uploaded_at', 'asc')
                    ->get();

                $allTableKeys = config('secreco.all_tables', [
                    'T1', 'T2a', 'T2b', 'T3', 'T4', 'T5', 'T6', 'T7a', 'T7b',
                    'T8a', 'T8b', 'T9', 'T10', 'T11', 'T12', 'T13', 'T14',
                    'T15', 'T16', 'T17', 'T18', 'T19', 'T20a', 'T20b',
                ]);

                $allResults = [];
                foreach ($allTableKeys as $tKey) {
                    $allResults[$tKey] = $this->buildTableData(
                        $tKey,
                        $year,
                        $allCmiUsers,
                        $subRows,
                        $allDirectRows->where('table_no', strtoupper($tKey)),
                        $allDocs->where('table_no', strtoupper($tKey)),
                        $statusPriority
                    );
                }

                $lockedTables = \App\Models\FormatTemplate::where('year', $year)
                    ->where('is_locked', true)
                    ->pluck('table_no')
                    ->map(fn($t) => strtoupper($t))
                    ->values()
                    ->toArray();

                return response()->json([
                    'ok'            => true,
                    'year'          => $year,
                    'table'         => 'all',
                    'data'          => $allResults,
                    'tables'        => $allResults,
                    'locked_tables' => $lockedTables,
                ]);
            }

            // Single table query
            $tableUpper = strtoupper($table);
            $directRows = ReportTable::where('reporting_year', $year)
                ->where('table_no', $tableUpper)
                ->whereNotIn('status', ['deleted'])
                ->whereHas('user', function ($q) {
                    $q->where('role', 'cmi');
                })
                ->with('user')
                ->get();

            $docs = ReportTableDoc::where('reporting_year', $year)
                ->where('table_no', $tableUpper)
                ->orderBy('sort_order', 'asc')
                ->orderBy('uploaded_at', 'asc')
                ->get();

            $result = $this->buildTableData(
                $table,
                $year,
                $allCmiUsers,
                $subRows,
                $directRows,
                $docs,
                $statusPriority
            );

            $lockedTables = \App\Models\FormatTemplate::where('year', $year)
                ->where('is_locked', true)
                ->pluck('table_no')
                ->map(fn($t) => strtoupper($t))
                ->values()
                ->toArray();

            return response()->json([
                'ok'            => true,
                'year'          => $year,
                'table'         => $table,
                'data'          => $result,
                'locked_tables' => $lockedTables,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('ReportController getConsolidated error: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return response()->json([
                'ok'      => false,
                'error'   => $e->getMessage(),
                'data'    => [],
                'message' => 'Failed to load consolidated report: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function buildTableData($table, $year, $allCmiUsers, $subRows, $directRows, $docs, array $statusPriority): array
    {
        $tableUpper = strtoupper($table);
        $tableLower = strtolower($table);

        // Build a map: user_id => result entry
        $resultMap = [];
        foreach ($subRows as $row) {
            $snap  = $row->snapshot_json ?? [];
            $uid   = $row->user_id;
            $tData = $snap[$table] ?? ($snap[$tableUpper] ?? ($snap[$tableLower] ?? null));
            $subStatus = $row->status ?? 'submitted';
            $tStatus = is_array($tData) ? ($tData['status'] ?? $subStatus) : $subStatus;
            if ($tStatus === 'done') {
                $tStatus = 'submitted';
            }

            $isAccepted = ($tStatus === 'accepted');

            $resultMap[$uid] = [
                'user_id'      => $uid,
                'institution'  => $row->user?->institution ?: $row->user?->name,
                'submitted_at' => $row->submitted_at ? $row->submitted_at->toDateTimeString() : null,
                'sub_status'   => $subStatus,
                'table_status' => $tStatus,
                'updated_at'   => is_array($tData) ? ($tData['updated_at'] ?? null) : null,
                'rows'         => $isAccepted && is_array($tData) ? ($tData['rows'] ?? []) : [],
                'meta'         => $isAccepted && is_array($tData) ? ($tData['meta'] ?? null) : null,
                'docs'         => [],
            ];
        }

        // Direct report_tables (only override with rows if THIS table is accepted)
        foreach ($directRows as $rt) {
            $uid = $rt->user_id;
            $st  = $rt->status ?? 'draft';
            $isAccepted = ($st === 'accepted');

            if (isset($resultMap[$uid])) {
                if ($isAccepted) {
                    $resultMap[$uid]['rows'] = $rt->rows_json ?? [];
                    $resultMap[$uid]['meta'] = $rt->meta_json ?? null;
                    $resultMap[$uid]['table_status'] = 'accepted';
                    $resultMap[$uid]['updated_at'] = $rt->updated_at ? $rt->updated_at->toDateTimeString() : $resultMap[$uid]['updated_at'];
                }
            } else {
                $resultMap[$uid] = [
                    'user_id'      => $uid,
                    'institution'  => $rt->user?->institution ?: $rt->user?->name,
                    'submitted_at' => null,
                    'sub_status'   => $isAccepted ? 'accepted' : 'draft',
                    'table_status' => $st,
                    'updated_at'   => $rt->updated_at ? $rt->updated_at->toDateTimeString() : null,
                    'rows'         => $isAccepted ? ($rt->rows_json ?? []) : [],
                    'meta'         => $isAccepted ? ($rt->meta_json ?? null) : null,
                    'docs'         => [],
                ];
            }
        }

        // All CMI users fallback
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

        // Group docs by user_id (only attach if accepted)
        $groupedDocs = $docs->groupBy('user_id');
        foreach ($resultMap as $uid => &$entry) {
            if (isset($groupedDocs[$uid]) && ($entry['table_status'] === 'accepted' || $entry['sub_status'] === 'accepted')) {
                $entry['docs'] = $groupedDocs[$uid]->toArray();
            }
        }
        unset($entry);

        // De-duplicate by institution
        $byInstitution = [];
        foreach ($resultMap as $entry) {
            $inst = $entry['institution'] ?? 'Unknown';
            if (!isset($byInstitution[$inst])) {
                $byInstitution[$inst] = $entry;
            } else {
                $curHasRows = !empty($byInstitution[$inst]['rows']);
                $newHasRows = !empty($entry['rows']);
                if ($newHasRows && !$curHasRows) {
                    $byInstitution[$inst] = $entry;
                } elseif ($newHasRows === $curHasRows) {
                    $cur = $statusPriority[$byInstitution[$inst]['table_status']] ?? 0;
                    $new = $statusPriority[$entry['table_status']] ?? 0;
                    if ($new > $cur) {
                        $byInstitution[$inst] = $entry;
                    }
                }
            }
        }

        $result = array_values($byInstitution);

        usort($result, function ($a, $b) use ($statusPriority) {
            $pa = $statusPriority[$a['table_status']] ?? 0;
            $pb = $statusPriority[$b['table_status']] ?? 0;
            if ($pa !== $pb) return $pb - $pa;
            return strcmp($a['institution'] ?? '', $b['institution'] ?? '');
        });

        return $result;
    }
}
