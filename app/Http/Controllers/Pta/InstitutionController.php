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
        $fmtCount    = \App\Models\FormatTemplate::where('year', $year)->count();
        $totalTables = $fmtCount > 0 ? $fmtCount : (count(config('secreco.all_tables', [])) ?: 24);

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

    public function details(Request $request): JsonResponse
    {
        $instName = trim((string) $request->input('institution', ''));
        $year     = (int) ($request->input('year') ?? date('Y'));

        $masterList = config('secreco.institutions', []);

        // Resolve metadata
        $meta = null;
        $matchedName = $instName;
        foreach ($masterList as $name => $m) {
            if (strcasecmp($name, $instName) === 0 || str_contains(strtolower($name), strtolower($instName)) || str_contains(strtolower($instName), strtolower($name))) {
                $meta = $m;
                $matchedName = $name;
                break;
            }
        }
        if (!$meta) {
            $matchedName = array_key_first($masterList) ?? 'Isabela State University - Echague';
            $meta = $masterList[$matchedName] ?? ['short' => 'ISU-E', 'type' => 'State University', 'logo' => 1];
        }

        $logoId  = $meta['logo'] ?? 1;
        $ext     = in_array((int)$logoId, [25, 26], true) ? 'png' : 'jpg';
        $logoUrl = "/assets/img/logo{$logoId}.{$ext}";

        $locations = [
            'Isabela State University - Echague' => 'Echague, Isabela',
            'Isabela State University - Cabagan' => 'Cabagan, Isabela',
            'Batanes State College' => 'Basco, Batanes',
            'Cagayan State University' => 'Tuguegarao City, Cagayan',
            'Nueva Vizcaya State University' => 'Bayombong, Nueva Vizcaya',
            'Quirino State University' => 'Diffun, Quirino',
            'University of La Salette' => 'Santiago City, Isabela',
            'DA - Agricultural Training Institute Region II' => 'San Mateo, Isabela',
            'DA - Regional Field Office 2' => 'Tuguegarao City, Cagayan',
            'Bureau of Fisheries & Aquatic Resources - R2' => 'Tuguegarao City, Cagayan',
            'Department of Environment and Natural Resources - Region II' => 'Tuguegarao City, Cagayan',
            'Department of Science and Technology - Region II' => 'Tuguegarao City, Cagayan',
            'Department of Trade and Industry - Region II' => 'Tuguegarao City, Cagayan',
            'Department of Economy, Planning and Development - Region II' => 'Tuguegarao City, Cagayan',
            'National Tobacco Administration' => 'Tuguegarao City, Cagayan',
            'DA - Philippine Rice Research Institute - Isabela' => 'San Mateo, Isabela',
            'Philippine Council for Agriculture, Aquatic and Natural Resources Research and Development' => 'Los Baños, Laguna',
            'DA - Bureau of Agricultural Research' => 'Diliman, Quezon City',
            'Watershed & Water Resources Research Development and Extension Center' => 'Los Baños, Laguna',
            'Mabuwaya Foundation Inc.' => 'Cabagan, Isabela',
            'Government City of Santiago' => 'Santiago City, Isabela',
            'Commission on Higher Education - Regional Office 2' => 'Tuguegarao City, Cagayan',
        ];

        $location = $locations[$matchedName] ?? 'Region 2, Philippines';

        // Find users for this institution
        $cmiUsers = User::where('role', 'cmi')
            ->where(function ($q) use ($matchedName, $instName) {
                $q->where('institution', $matchedName)
                  ->orWhere('institution', $instName);
            })
            ->get();
        $uIds = $cmiUsers->pluck('id')->all();

        // Find official submission
        $sub = null;
        if ($uIds) {
            $sub = ReportSubmission::where('reporting_year', $year)
                ->whereIn('user_id', $uIds)
                ->orderByDesc('submitted_at')
                ->first();
        }

        $fmtTables = \App\Models\FormatTemplate::where('year', $year)->orderBy('sort_order', 'asc')->pluck('table_no')->toArray();
        if (!empty($fmtTables)) {
            $allTableKeys = $fmtTables;
        } else {
            $allTableKeys = config('secreco.all_tables', [
                'T1', 'T2a', 'T2b', 'T3', 'T4', 'T5', 'T6', 'T7a', 'T7b',
                'T8a', 'T8b', 'T9', 'T10', 'T11', 'T12', 'T13', 'T14',
                'T15', 'T16', 'T17', 'T18', 'T19', 'T20a', 'T20b'
            ]);
        }

        $shortTitles = [
            'T1'   => 'AIHRs',
            'T2a'  => 'RSRDH Summary',
            'T2b'  => 'RSRDH Participants',
            'T3'   => 'Projects Monitored',
            'T4'   => 'Resources Shared',
            'T5'   => 'Resources Generated',
            'T6'   => 'Linkages',
            'T7a'  => 'Databases',
            'T7b'  => 'Info Systems',
            'T8a'  => 'R&D Programs',
            'T8b'  => 'Collaborative R&D',
            'T9'   => 'Technologies Generated',
            'T10'  => 'TT Projects',
            'T11'  => 'Technologies Extended',
            'T12'  => 'Commercialized',
            'T13'  => 'Tech Promotion',
            'T14'  => 'Trainings',
            'T15'  => 'Equipment Facilities',
            'T16'  => 'Awards',
            'T17'  => 'Meetings',
            'T18'  => 'CMI Contributions',
            'T19'  => 'Governance',
            'T20a' => 'Policy Researches',
            'T20b' => 'Policies',
        ];

        $tablesData = [];
        $submittedCount = 0;
        $inProgressCount = 0;
        $notStartedCount = 0;

        $userTables = [];
        if ($uIds) {
            $dbTables = ReportTable::where('reporting_year', $year)
                ->whereIn('user_id', $uIds)
                ->get();
            foreach ($dbTables as $tr) {
                $clean = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $tr->table_no));
                $userTables[$clean] = $tr;
            }
        }

        $hasActiveSub = $sub && in_array($sub->status, ['submitted', 'pending', 'accepted', 'returned'], true);

        foreach ($allTableKeys as $tNo) {
            $cleanKey = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $tNo));
            $tr = $userTables[$cleanKey] ?? null;

            $status = 'Not Started';
            $rowCount = '—';
            $updatedAt = '—';

            if ($tr) {
                $numRows = is_array($tr->rows_json) ? count($tr->rows_json) : 0;
                if ($numRows > 0) {
                    $rowCount = (string) $numRows;
                }
                if ($tr->updated_at) {
                    $m = date('M', strtotime((string)$tr->updated_at));
                    if ($m === 'Sep') $m = 'Sept';
                    $updatedAt = $m . '. ' . date('j, Y', strtotime((string)$tr->updated_at));
                }

                $st = $tr->status;
                if ($st === 'done' || $st === 'submitted' || $st === 'accepted') {
                    $status = 'Submitted';
                    $submittedCount++;
                } elseif ($st === 'draft' || $numRows > 0 || !empty($tr->meta_json)) {
                    $status = 'In Progress';
                    $inProgressCount++;
                } else {
                    $status = 'Not Started';
                    $notStartedCount++;
                }
            } else {
                $notStartedCount++;
            }

            $tablesData[] = [
                'table_no' => $tNo,
                'title'    => $shortTitles[$tNo] ?? $tNo,
                'status'   => $status,
                'rows'     => $rowCount,
                'updated'  => $updatedAt,
            ];
        }

        $totalTables = count($allTableKeys);
        $completedCount = $submittedCount + $inProgressCount;
        $progressPct = $totalTables > 0 ? round(($completedCount / $totalTables) * 100) : 0;

        return response()->json([
            'ok'          => true,
            'year'        => $year,
            'institution' => [
                'name'         => $matchedName,
                'short'        => $meta['short'] ?? '',
                'type'         => $meta['type'] ?? 'State University',
                'location'     => $location,
                'logo_url'     => $logoUrl,
                'encoder'      => $cmiUsers->pluck('name')->filter()->join(', ') ?: '—',
                'progress_pct' => $progressPct,
                'completed'    => $completedCount,
                'total'        => $totalTables,
                'submitted'    => $submittedCount,
                'in_progress'  => $inProgressCount,
                'not_started'  => $notStartedCount,
            ],
            'tables' => $tablesData,
        ]);
    }
}
