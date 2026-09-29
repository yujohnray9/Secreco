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

                $templateTableKeys = \App\Models\FormatTemplate::where('year', $year)
                    ->orderBy('sort_order', 'asc')
                    ->pluck('table_no')
                    ->toArray();
                $reportedTableKeys = ReportTable::where('reporting_year', $year)
                    ->pluck('table_no')
                    ->toArray();

                if (!empty($templateTableKeys)) {
                    $allTableKeys = array_values(array_unique(array_merge($templateTableKeys, $reportedTableKeys)));
                } else {
                    $defaultTables = config('secreco.all_tables', [
                        'T1', 'T2a', 'T2b', 'T3', 'T4', 'T5', 'T6', 'T7a', 'T7b',
                        'T8a', 'T8b', 'T9', 'T10', 'T11', 'T12', 'T13', 'T14',
                        'T15', 'T16', 'T17', 'T18', 'T19', 'T20a', 'T20b',
                    ]);
                    $allTableKeys = array_values(array_unique(array_merge($defaultTables, $reportedTableKeys)));
                }

                $allResults = [];
                foreach ($allTableKeys as $tKey) {
                    $cleanKey = preg_replace('/[^A-Za-z0-9]/', '', $tKey);
                    $tCandidates = array_values(array_unique(array_filter([
                        $tKey,
                        strtoupper($tKey),
                        strtolower($tKey),
                        ucfirst($tKey),
                        $cleanKey,
                        strtoupper($cleanKey),
                        strtolower($cleanKey),
                        ucfirst($cleanKey),
                    ])));

                    $allResults[$tKey] = $this->buildTableData(
                        $tKey,
                        $year,
                        $allCmiUsers,
                        $subRows,
                        $allDirectRows->filter(function ($r) use ($tCandidates) {
                            return in_array($r->table_no, $tCandidates, true);
                        }),
                        $allDocs->filter(function ($d) use ($tCandidates) {
                            return in_array($d->table_no, $tCandidates, true);
                        }),
                        $statusPriority
                    );
                }

                $lockedTables = \App\Models\FormatTemplate::where('year', $year)
                    ->where('is_locked', true)
                    ->pluck('table_no')
                    ->map(fn($t) => strtoupper($t))
                    ->values()
                    ->toArray();

                $templates = \App\Models\FormatTemplate::where('year', $year)
                    ->get()
                    ->keyBy('table_no');

                return response()->json([
                    'ok'            => true,
                    'year'          => $year,
                    'table'         => 'all',
                    'data'          => $allResults,
                    'tables'        => $allResults,
                    'templates'     => $templates,
                    'locked_tables' => $lockedTables,
                ]);
            }

            // Single table query
            $clean = preg_replace('/[^A-Za-z0-9]/', '', $table);
            $candidates = array_values(array_unique(array_filter([
                $table,
                strtoupper($table),
                strtolower($table),
                ucfirst($table),
                $clean,
                strtoupper($clean),
                strtolower($clean),
                ucfirst($clean),
            ])));

            $directRows = ReportTable::where('reporting_year', $year)
                ->whereIn('table_no', $candidates)
                ->whereNotIn('status', ['deleted'])
                ->whereHas('user', function ($q) {
                    $q->where('role', 'cmi');
                })
                ->with('user')
                ->get();

            $docs = ReportTableDoc::where('reporting_year', $year)
                ->whereIn('table_no', $candidates)
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

            $template = \App\Models\FormatTemplate::where('year', $year)
                ->whereIn('table_no', $candidates)
                ->first();

            return response()->json([
                'ok'            => true,
                'year'          => $year,
                'table'         => $table,
                'data'          => $result,
                'template'      => $template,
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
        $clean = preg_replace('/[^A-Za-z0-9]/', '', $table);
        $candidates = array_values(array_unique(array_filter([
            $table,
            strtoupper($table),
            strtolower($table),
            ucfirst($table),
            $clean,
            strtoupper($clean),
            strtolower($clean),
            ucfirst($clean),
        ])));

        // Build a map: user_id => result entry
        $resultMap = [];
        foreach ($subRows as $row) {
            $snap  = $row->snapshot_json ?? [];
            $uid   = $row->user_id;

            $tData = null;
            foreach ($candidates as $cand) {
                if (isset($snap[$cand])) {
                    $tData = $snap[$cand];
                    break;
                }
            }
            $subStatus = $row->status ?? 'submitted';
            $tStatus = is_array($tData) ? ($tData['status'] ?? $subStatus) : $subStatus;
            if ($tStatus === 'done') {
                $tStatus = 'submitted';
            }

            $rows = (is_array($tData) && !empty($tData['rows'])) ? $tData['rows'] : [];
            $meta = is_array($tData) ? ($tData['meta'] ?? null) : null;

            $resultMap[$uid] = [
                'user_id'      => $uid,
                'institution'  => $row->user?->institution ?: $row->user?->name,
                'submitted_at' => $row->submitted_at ? $row->submitted_at->toDateTimeString() : null,
                'sub_status'   => $subStatus,
                'table_status' => $tStatus,
                'updated_at'   => is_array($tData) ? ($tData['updated_at'] ?? null) : null,
                'rows'         => $rows,
                'meta'         => $meta,
                'docs'         => [],
            ];
        }

        // Direct report_tables (incorporate direct CMI input/saved data)
        foreach ($directRows as $rt) {
            $uid = $rt->user_id;
            $st  = $rt->status ?? 'draft';
            $rtRows = (is_array($rt->rows_json) && !empty($rt->rows_json)) ? $rt->rows_json : [];
            $rtMeta = $rt->meta_json ?? null;
            $rtUpdated = $rt->updated_at ? $rt->updated_at->toDateTimeString() : null;

            if (isset($resultMap[$uid])) {
                $existingRows = $resultMap[$uid]['rows'] ?? [];
                $existingStatus = $resultMap[$uid]['table_status'] ?? 'not-started';
                $pNew = $statusPriority[$st] ?? 0;
                $pOld = $statusPriority[$existingStatus] ?? 0;

                if (!empty($rtRows) || $pNew >= $pOld) {
                    if (!empty($rtRows) || empty($existingRows)) {
                        $resultMap[$uid]['rows'] = $rtRows;
                    }
                    if ($rtMeta) {
                        $resultMap[$uid]['meta'] = $rtMeta;
                    }
                    if ($pNew >= $pOld || empty($existingStatus) || $existingStatus === 'not-started') {
                        $resultMap[$uid]['table_status'] = $st;
                    }
                    if ($rtUpdated) {
                        $resultMap[$uid]['updated_at'] = $rtUpdated;
                    }
                }
            } else {
                $resultMap[$uid] = [
                    'user_id'      => $uid,
                    'institution'  => $rt->user?->institution ?: $rt->user?->name,
                    'submitted_at' => null,
                    'sub_status'   => $st === 'accepted' ? 'accepted' : 'draft',
                    'table_status' => $st,
                    'updated_at'   => $rtUpdated,
                    'rows'         => $rtRows,
                    'meta'         => $rtMeta,
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

        // Group docs by user_id
        $groupedDocs = $docs->groupBy('user_id');
        foreach ($resultMap as $uid => &$entry) {
            if (isset($groupedDocs[$uid])) {
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
