<?php

namespace App\Http\Controllers\Pta;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ReportSubmission;
use App\Models\ReportTable;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function getStats(Request $request): JsonResponse
    {
        $year = (int) ($request->input('year') ?? date('Y'));

        $cmiList = User::where('role', 'cmi')->where('status', 'active')->orderBy('institution')->get();
        $totalCMIs = $cmiList->count();

        $allTables = ReportTable::where('reporting_year', $year)
            ->whereHas('user', function ($q) {
                $q->where('role', 'cmi')->where('status', 'active');
            })->get();

        $tablesByUser = [];
        foreach ($allTables as $row) {
            $tablesByUser[$row->user_id][] = $row;
        }

        $templates = \App\Models\FormatTemplate::where('year', $year)
            ->orderBy('sort_order', 'asc')
            ->orderBy('table_no', 'asc')
            ->get();

        if ($templates->isNotEmpty()) {
            $sectionMap = [];
            $allTableKeys = [];
            foreach ($templates as $t) {
                $sec = trim($t->section) ?: 'General';
                $sectionMap[$sec][] = $t->table_no;
                $allTableKeys[] = $t->table_no;
            }
            $TOTAL_TABLES = count($allTableKeys);
        } else {
            $sectionMap = config('secreco.sections');
            $TOTAL_TABLES = 24;
        }

        $submitted  = 0;
        $inProgress = 0;
        $notStarted = 0;
        $returned   = 0;
        $accepted   = 0;
        $cmiStatusList = [];

        $submissions = ReportSubmission::where('reporting_year', $year)->orderByDesc('submitted_at')->get();
        $latestSub = [];
        foreach ($submissions as $s) {
            if (!isset($latestSub[$s->user_id])) {
                $latestSub[$s->user_id] = $s;
            }
        }

        foreach ($cmiList as $cmi) {
            $uid       = $cmi->id;
            $uTables   = $tablesByUser[$uid] ?? [];
            $done      = count(array_filter($uTables, fn($t) => in_array($t->status, ['done', 'submitted', 'accepted'], true)));
            $hasTables = count($uTables) > 0;

            $sub       = $latestSub[$uid] ?? null;
            $subStatus = $sub?->status;

            if ($subStatus === 'accepted') {
                $overallStatus = 'Accepted';
                $accepted++;
            } elseif ($subStatus === 'returned') {
                $overallStatus = 'Returned';
                $returned++;
            } elseif ($subStatus && in_array($subStatus, ['pending', 'submitted'], true)) {
                $overallStatus = 'Submitted';
                $submitted++;
            } elseif ($hasTables) {
                $overallStatus = 'In Progress';
                $inProgress++;
            } else {
                $overallStatus = 'Not Started';
                $notStarted++;
            }

            $cmiStatusList[] = [
                'institution'  => $cmi->institution ?? '—',
                'encoder'      => $cmi->name,
                'designation'  => $cmi->designation ?? '—',
                'tables_done'  => $done,
                'total_tables' => $TOTAL_TABLES,
                'status'       => $overallStatus,
                'submitted_at' => $sub?->submitted_at ? $sub->submitted_at->toDateTimeString() : null,
            ];
        }

        $sectionProgress = [];
        foreach ($sectionMap as $sectionLabel => $tableKeys) {
            $totalSlots = count($cmiList) * count($tableKeys);
            if ($totalSlots === 0) {
                $sectionProgress[] = ['label' => $sectionLabel, 'pct' => 0];
                continue;
            }
            $doneCount = 0;
            $cleanTableKeys = array_map(fn($k) => strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $k)), $tableKeys);
            foreach ($allTables as $row) {
                $cleanRowTable = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $row->table_no));
                if (in_array($cleanRowTable, $cleanTableKeys, true) && in_array($row->status, ['done', 'submitted', 'accepted'], true)) {
                    $doneCount++;
                }
            }
            $pct = round(($doneCount / $totalSlots) * 100);
            $sectionProgress[] = ['label' => $sectionLabel, 'pct' => $pct];
        }

        $recentActivity = ActivityLog::with('user')->orderByDesc('created_at')->take(10)->get()
            ->map(fn($al) => [
                'description' => $al->description,
                'created_at'  => $al->created_at ? $al->created_at->format('Y-m-d\TH:i:s+08:00') : null,
                'actor'       => $al->user ? $al->user->name : 'System',
            ]);

        $pendingApprovals = User::where('status', 'pending')->whereIn('role', ['cmi', 'viewer'])->count();

        $trendData = ReportTable::where('status', 'done')
            ->where('reporting_year', $year)
            ->where('updated_at', '>=', now()->subDays(14)->startOfDay())
            ->selectRaw('DATE(updated_at) as date, COUNT(*) as cnt')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $trend = [];
        for ($i = 13; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $trend[$d] = 0;
        }
        foreach ($trendData as $row) {
            if (isset($trend[$row->date])) {
                $trend[$row->date] = (int) $row->cnt;
            }
        }

        $trendLabels = array_map(fn($d) => date('M j', strtotime($d)), array_keys($trend));
        $trendValues = array_values($trend);

        return response()->json([
            'ok'   => true,
            'year' => $year,
            'stats' => [
                'total_cmis'        => $totalCMIs,
                'submitted'         => $submitted,
                'in_progress'       => $inProgress,
                'not_started'       => $notStarted,
                'returned'          => $returned,
                'accepted'          => $accepted,
                'pending_approvals' => $pendingApprovals,
            ],
            'section_progress' => $sectionProgress,
            'recent_activity'  => $recentActivity,
            'cmi_list'         => $cmiStatusList,
            'trend_labels'     => $trendLabels,
            'trend_values'     => $trendValues,
        ]);
    }
}
