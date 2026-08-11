<?php

namespace App\Http\Controllers\Cmi;

use App\Http\Controllers\Controller;
use App\Models\ReportSubmission;
use App\Models\ReportTable;
use App\Models\ReportTableDoc;
use DateTime;
use DateTimeZone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function submit(Request $request): JsonResponse
    {
        $userId        = Auth::id() ?? session('user_id');
        $reportingYear = (int) ($request->input('year') ?? $request->input('reporting_year') ?? date('Y'));
        $allTables     = config('secreco.all_tables', [
            'T1','T2a','T2b','T3','T4','T5','T6','T7a','T7b',
            'T8a','T8b','T9','T10','T11','T12','T13','T14','T15',
            'T16','T17','T18','T19','T20a','T20b'
        ]);

        // Update ALL tables with content for this user and year to 'done' on submit
        ReportTable::where('user_id', $userId)
            ->where('reporting_year', $reportingYear)
            ->get()
            ->each(function ($tbl) {
                $hasRows = is_array($tbl->rows_json) && count($tbl->rows_json) > 0;
                $hasMeta = is_array($tbl->meta_json) && count($tbl->meta_json) > 0;
                if ($hasRows || $hasMeta || $tbl->status === 'draft') {
                    $tbl->update(['status' => 'done', 'updated_at' => now()]);
                }
            });

        $saved = ReportTable::where('user_id', $userId)
            ->where('reporting_year', $reportingYear)
            ->get();

        $byTable = [];
        foreach ($saved as $row) {
            $byTable[$row->table_no] = $row;
        }

        $snapshot = [];
        foreach ($allTables as $no) {
            if (isset($byTable[$no])) {
                $snapshot[$no] = [
                    'status'     => $byTable[$no]->status,
                    'meta'       => $byTable[$no]->meta_json,
                    'rows'       => $byTable[$no]->rows_json,
                    'updated_at' => $byTable[$no]->updated_at ? $byTable[$no]->updated_at->toDateTimeString() : null,
                ];
            } else {
                $snapshot[$no] = [
                    'status'     => 'not-started',
                    'meta'       => null,
                    'rows'       => [],
                    'updated_at' => null,
                ];
            }
        }

        $docs = ReportTableDoc::where('user_id', $userId)
            ->where('reporting_year', $reportingYear)
            ->orderBy('table_no')
            ->orderBy('sort_order')
            ->orderBy('uploaded_at')
            ->get();

        foreach ($docs as $doc) {
            $snapshot[$doc->table_no]['docs'][] = $doc->toArray();
        }

        $summary = ['done' => 0, 'draft' => 0, 'not-started' => 0, 'error' => 0];
        foreach ($allTables as $no) {
            $st = $snapshot[$no]['status'] ?? 'not-started';
            $summary[$st] = ($summary[$st] ?? 0) + 1;
        }

        $nowManila       = new DateTime('now', new DateTimeZone('Asia/Manila'));
        $submittedAtStr  = $nowManila->format('Y-m-d H:i:s');

        $submission = ReportSubmission::create([
            'user_id'        => $userId,
            'reporting_year' => $reportingYear,
            'submitted_at'   => $submittedAtStr,
            'status'         => 'pending',
            'snapshot_json'  => $snapshot,
        ]);

        return response()->json([
            'success'       => true,
            'submission_id' => $submission->id,
            'submitted_at'  => $submittedAtStr,
            'summary'       => $summary,
        ]);
    }
}
