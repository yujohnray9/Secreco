<?php

namespace App\Http\Controllers\Pta;

use App\Http\Controllers\Controller;
use App\Models\ReportSubmission;
use App\Models\ReportTable;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InstitutionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $year        = (int) ($request->input('year') ?? date('Y'));
        $totalTables = 20;

        $masterList = config('secreco.institutions');

        $cmiUsers = User::where('role', 'cmi')->where('status', 'active')->get();
        $cmiUserIds = $cmiUsers->pluck('id')->all();

        $submissions = ReportSubmission::where('reporting_year', $year)
            ->whereIn('user_id', $cmiUserIds)
            ->orderByDesc('submitted_at')
            ->get()
            ->unique('user_id')
            ->keyBy('user_id');

        $doneCounts = ReportTable::where('reporting_year', $year)
            ->where('status', 'done')
            ->whereIn('user_id', $cmiUserIds)
            ->selectRaw('user_id, COUNT(DISTINCT table_no) as cnt')
            ->groupBy('user_id')
            ->pluck('cnt', 'user_id');

        $byInst = [];
        foreach ($cmiUsers as $u) {
            $sub = $submissions->get($u->id);
            $byInst[$u->institution] = [
                'encoder'      => $u->name,
                'tables_done'  => (int) ($doneCounts->get($u->id) ?? 0),
                'sub_status'   => $sub?->status,
                'submitted_at' => $sub?->submitted_at ? $sub->submitted_at->toDateTimeString() : null,
                'has_cmi'      => true,
            ];
        }

        $institutions = [];
        $idx = 1;
        foreach ($masterList as $instName => $meta) {
            $db = $byInst[$instName] ?? null;

            $tablesDone = (int) ($db['tables_done'] ?? 0);
            $subStatus  = $db['sub_status'] ?? null;

            if ($subStatus === 'accepted' || $subStatus === 'submitted') {
                $status = 'Submitted';
            } elseif ($subStatus === 'returned') {
                $status = 'Returned';
            } elseif ($tablesDone > 0 || $subStatus === 'in-progress') {
                $status = 'In Progress';
            } else {
                $status = 'Not Started';
            }

            $logoId  = $meta['logo'] ?? $idx;
            $logoUrl = "/secreco/assets/img/logo{$logoId}.jpg";
            $idx++;

            $institutions[] = [
                'name'         => $instName,
                'short'        => $meta['short'],
                'type'         => $meta['type'],
                'encoder'      => $db ? $db['encoder'] : '—',
                'has_cmi'      => (bool) $db,
                'tables_done'  => $tablesDone,
                'total_tables' => $totalTables,
                'status'       => $status,
                'sub_status'   => $subStatus,
                'submitted_at' => $db['submitted_at'] ?? null,
                'logo_url'     => $logoUrl,
            ];
        }

        $summary = [
            'total'       => count($institutions),
            'submitted'   => count(array_filter($institutions, fn($i) => $i['status'] === 'Submitted')),
            'in_progress' => count(array_filter($institutions, fn($i) => $i['status'] === 'In Progress')),
            'not_started' => count(array_filter($institutions, fn($i) => $i['status'] === 'Not Started')),
            'returned'    => count(array_filter($institutions, fn($i) => $i['status'] === 'Returned')),
            'no_cmi'      => count(array_filter($institutions, fn($i) => !$i['has_cmi'])),
        ];

        return response()->json([
            'ok'           => true,
            'year'         => $year,
            'institutions' => $institutions,
            'summary'      => $summary,
        ]);
    }
}
