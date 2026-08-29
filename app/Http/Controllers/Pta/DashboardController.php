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
            $sectionMap = config('secreco.sections') ?? [];
            $allTableKeys = [];
            if (is_array($sectionMap)) {
                foreach ($sectionMap as $sTables) {
                    if (is_array($sTables)) {
                        foreach ($sTables as $st) $allTableKeys[] = $st;
                    }
                }
            }
            $TOTAL_TABLES = count($allTableKeys) > 0 ? count($allTableKeys) : 24;
        }

        $tableSubmitted  = 0;
        $tableInProgress = 0;
        $tableNotStarted = 0;
        $tableReturned   = 0;
        $tableAccepted   = 0;

        $cmiSubmitted    = 0;
        $cmiInProgress   = 0;
        $cmiNotStarted   = 0;
        $cmiReturned     = 0;
        $cmiAccepted     = 0;
        $cmiStatusList   = [];

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
            $userTablesByNo = [];
            foreach ($uTables as $tRow) {
                $cleanKey = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $tRow->table_no));
                $userTablesByNo[$cleanKey] = $tRow;
            }

            $done      = count(array_filter($uTables, fn($t) => in_array($t->status, ['done', 'submitted', 'accepted'], true)));
            $hasTables = count($uTables) > 0;

            $sub       = $latestSub[$uid] ?? null;
            $subStatus = $sub?->status;

            if ($subStatus === 'accepted') {
                $overallStatus = 'Accepted';
                $cmiAccepted++;
            } elseif ($subStatus === 'returned') {
                $overallStatus = 'Returned';
                $cmiReturned++;
            } elseif ($subStatus && in_array($subStatus, ['pending', 'submitted'], true)) {
                $overallStatus = 'Submitted';
                $cmiSubmitted++;
            } elseif ($hasTables) {
                $overallStatus = 'In Progress';
                $cmiInProgress++;
            } else {
                $overallStatus = 'Not Started';
                $cmiNotStarted++;
            }

            // Calculate table-level statuses for this CMI
            $hasActiveSub = $sub && in_array($subStatus, ['pending', 'submitted', 'accepted', 'returned'], true);
            $snap = (is_array($sub?->snapshot_json)) ? $sub->snapshot_json : [];

            foreach ($allTableKeys as $tNo) {
                $cleanKey = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $tNo));
                $tr = $userTablesByNo[$cleanKey] ?? null;
                $snapTable = null;
                foreach ([$tNo, $cleanKey, strtolower($tNo)] as $candidate) {
                    if (isset($snap[$candidate]) && is_array($snap[$candidate])) {
                        $snapTable = $snap[$candidate];
                        break;
                    }
                }

                $st = $tr?->status;
                $snapSt = $snapTable['status'] ?? null;

                if ($st === 'accepted' || $snapSt === 'accepted') {
                    $tableAccepted++;
                } elseif ($st === 'returned' || $snapSt === 'returned') {
                    $tableReturned++;
                } elseif ($hasActiveSub && (in_array($st, ['submitted', 'done'], true) || in_array($snapSt, ['submitted', 'done'], true))) {
                    $tableSubmitted++;
                } elseif ($st === 'draft' || ($st === 'done' && !$hasActiveSub)) {
                    $tableInProgress++;
                } else {
                    $tableNotStarted++;
                }
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

        // ── 1. WEEKLY TREND (Last 7 Days) ──
        $weeklyTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $weeklyTrend[$d] = 0;
        }

        $weeklyData = ReportSubmission::where('reporting_year', $year)
            ->whereNotNull('submitted_at')
            ->where('submitted_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(submitted_at) as date, COUNT(*) as cnt')
            ->groupBy('date')
            ->get();

        foreach ($weeklyData as $row) {
            if (isset($weeklyTrend[$row->date])) {
                $weeklyTrend[$row->date] = (int) $row->cnt;
            }
        }

        $trendLabels = array_map(fn($d) => date('M j', strtotime($d)), array_keys($weeklyTrend));
        $trendValues = array_values($weeklyTrend);

        // ── 2. MONTHLY TREND (CY Year Jan–Dec) ──
        $monthlyTrend = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthlyTrend[$m] = 0;
        }

        $monthlyData = ReportSubmission::where('reporting_year', $year)
            ->whereNotNull('submitted_at')
            ->whereYear('submitted_at', $year)
            ->selectRaw('MONTH(submitted_at) as month, COUNT(*) as cnt')
            ->groupBy('month')
            ->get();

        foreach ($monthlyData as $row) {
            $m = (int) $row->month;
            if (isset($monthlyTrend[$m])) {
                $monthlyTrend[$m] = (int) $row->cnt;
            }
        }

        $monthlyLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $monthlyValues = array_values($monthlyTrend);

        // ── 3. ANNUAL TREND (Last 5 Years) ──
        $currentYear = (int) ($year ?: date('Y'));
        $annualLabels = [];
        $annualTrend  = [];
        for ($y = $currentYear - 4; $y <= $currentYear; $y++) {
            $annualLabels[] = (string) $y;
            $annualTrend[$y] = 0;
        }

        $annualData = ReportSubmission::whereNotNull('submitted_at')
            ->whereBetween('reporting_year', [$currentYear - 4, $currentYear])
            ->selectRaw('reporting_year, COUNT(*) as cnt')
            ->groupBy('reporting_year')
            ->get();

        foreach ($annualData as $row) {
            $ry = (int) $row->reporting_year;
            if (isset($annualTrend[$ry])) {
                $annualTrend[$ry] = (int) $row->cnt;
            }
        }
        $annualValues = array_values($annualTrend);

        return response()->json([
            'ok'   => true,
            'year' => $year,
            'stats' => [
                'total_cmis'        => $totalCMIs,
                'submitted'         => $tableSubmitted,
                'in_progress'       => $tableInProgress,
                'not_started'       => $tableNotStarted,
                'returned'          => $tableReturned,
                'accepted'          => $tableAccepted,
                'cmi_submitted'     => $cmiSubmitted,
                'cmi_in_progress'   => $cmiInProgress,
                'cmi_not_started'   => $cmiNotStarted,
                'cmi_returned'      => $cmiReturned,
                'cmi_accepted'      => $cmiAccepted,
                'pending_approvals' => $pendingApprovals,
            ],
            'section_progress' => $sectionProgress,
            'recent_activity'  => $recentActivity,
            'cmi_list'         => $cmiStatusList,
            'cmi_status_list'  => $cmiStatusList,
            'trend_labels'     => $trendLabels,
            'trend_values'     => $trendValues,
            'monthly_labels'   => $monthlyLabels,
            'monthly_values'   => $monthlyValues,
            'annual_labels'    => $annualLabels,
            'annual_values'    => $annualValues,
        ]);
    }
}
