<?php

namespace App\Http\Controllers\Pta;

use App\Http\Controllers\Controller;
use App\Models\ReportSubmission;
use App\Models\ReportTableDoc;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function getConsolidated(Request $request): JsonResponse
    {
        $year       = (int) ($request->input('year') ?? date('Y'));
        $table      = trim($request->input('table', 'T1'));
        $tableUpper = strtoupper($table);

        $latestSubIds = ReportSubmission::where('reporting_year', $year)
            ->selectRaw('MAX(id) as max_id')
            ->groupBy('user_id')
            ->pluck('max_id');

        $rows = ReportSubmission::whereIn('id', $latestSubIds)
            ->whereHas('user', function ($q) {
                $q->where('role', 'cmi');
            })
            ->with('user')
            ->get();

        $allDocs = ReportTableDoc::where('reporting_year', $year)
            ->where('table_no', $tableUpper)
            ->orderBy('sort_order', 'asc')
            ->orderBy('uploaded_at', 'asc')
            ->get()
            ->groupBy('user_id');

        $result = [];
        foreach ($rows as $row) {
            $snap  = $row->snapshot_json ?? [];
            $uid   = $row->user_id;

            $tData = $snap[$table] ?? $snap[$tableUpper] ?? $snap[strtolower($table)] ?? null;

            $docs = isset($allDocs[$uid])
                ? $allDocs[$uid]->toArray()
                : ($tData['docs'] ?? []);

            $userInst = $row->user?->institution ?: $row->user?->name;

            $result[] = [
                'user_id'      => $uid,
                'institution'  => $userInst,
                'submitted_at' => $row->submitted_at ? $row->submitted_at->toDateTimeString() : null,
                'sub_status'   => $row->status,
                'table_status' => $tData['status']     ?? 'not-started',
                'updated_at'   => $tData['updated_at'] ?? null,
                'rows'         => $tData['rows']        ?? [],
                'meta'         => $tData['meta']        ?? null,
                'docs'         => $docs,
            ];
        }

        return response()->json(['ok' => true, 'year' => $year, 'table' => $table, 'data' => $result]);
    }
}
