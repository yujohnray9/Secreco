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
        $totalTables = count(config('secreco.all_tables', [])) ?: 24;

        $masterList = config('secreco.institutions');

        $cmiUsers = User::where('role', 'cmi')->where('status', 'active')->get();
        $cmiUsersByInst = $cmiUsers->groupBy('institution');

        $doneRows = ReportTable::where('reporting_year', $year)
            ->whereIn('status', ['done', 'submitted', 'accepted'])
            ->select('user_id', 'table_no')
            ->get()
            ->groupBy('user_id');

        $byInst = [];
        foreach ($cmiUsersByInst as $instName => $users) {
            $uIds = $users->pluck('id')->all();

            $sub = ReportSubmission::where('reporting_year', $year)
                ->whereIn('user_id', $uIds)
                ->orderByDesc('submitted_at')
                ->first();

            $distinctTables = [];
            foreach ($uIds as $uid) {
                $userTables = $doneRows->get($uid, collect());
                foreach ($userTables as $row) {
                    $cleanKey = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $row->table_no));
                    $distinctTables[$cleanKey] = true;
                }
            }

            $encoderNames = $users->pluck('name')->filter()->join(', ');

            $byInst[$instName] = [
                'encoder'      => $encoderNames ?: '—',
                'tables_done'  => count($distinctTables),
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
            $ext     = in_array((int)$logoId, [25, 26], true) ? 'png' : 'jpg';
            $logoUrl = "/assets/img/logo{$logoId}.{$ext}";
            $idx++;

            $firstUser = $cmiUsersByInst->get($instName)?->first();
            $userId = $firstUser?->id ?? 0;

            $institutions[] = [
                'name'         => $instName,
                'short'        => $meta['short'],
                'type'         => $meta['type'],
                'user_id'      => $userId,
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

        $lockedTables = \App\Models\FormatTemplate::where('year', $year)
            ->where('is_locked', true)
            ->pluck('table_no')
            ->map(fn($t) => strtoupper($t))
            ->values()
            ->toArray();

        return response()->json([
            'ok'            => true,
            'year'          => $year,
            'institutions'  => $institutions,
            'summary'       => $summary,
            'locked_tables' => $lockedTables,
        ]);
    }
}
