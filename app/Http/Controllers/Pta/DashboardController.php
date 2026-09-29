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

        // ── Group CMIs by Institution to calculate Institution-level metrics ──
        $masterList = config('secreco.institutions', []);
        $groupedCMIs = $cmiList->groupBy('institution');

        $instSubmitted  = 0;
        $instInProgress = 0;
        $instNotStarted = 0;
        $instAccepted   = 0;
        $instReturned   = 0;

        foreach ($masterList as $instName => $meta) {
            $users = $groupedCMIs->get($instName, collect());
            $uIds  = $users->pluck('id')->all();

            $sub = null;
            if ($uIds) {
                $sub = ReportSubmission::where('reporting_year', $year)
                    ->whereIn('user_id', $uIds)
                    ->orderByDesc('submitted_at')
                    ->first();
            }
            $subStatus = $sub?->status;

            $distinctTables = [];
            $hasAnyDraft = false;
            foreach ($uIds as $uid) {
                $userTables = $tablesByUser[$uid] ?? [];
                foreach ($userTables as $tr) {
                    $cleanKey = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $tr->table_no));
                    if (in_array($tr->status, ['done', 'submitted', 'accepted'], true)) {
                        $distinctTables[$cleanKey] = true;
                    }
                    if ($tr->status === 'draft' || !empty($tr->rows_json) || !empty($tr->meta_json)) {
                        $hasAnyDraft = true;
                    }
                }
            }

            $tablesDone = count($distinctTables);
            $isAllCompleted = ($tablesDone >= $TOTAL_TABLES);

            // "Submitted" = CMI has filled ALL tables AND officially submitted
            // "Accepted"  = PTA accepted the submission (all tables done + submitted + accepted)
            if ($subStatus === 'accepted' && $isAllCompleted) {
                $instAccepted++;
                $instSubmitted++;
            } elseif (in_array($subStatus, ['submitted', 'pending'], true) && $isAllCompleted) {
                // pending = submitted but awaiting PTA review
                $instSubmitted++;
            } elseif ($subStatus === 'returned') {
                // Returned for correction — still in progress
                $instReturned++;
                $instInProgress++;
            } elseif ($tablesDone > 0 || $hasAnyDraft || in_array($subStatus, ['submitted', 'pending', 'in-progress'], true)) {
                // Has some progress (or submitted without all tables done) — In Progress
                $instInProgress++;
            } else {
                $instNotStarted++;
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

        // ── Comprehensive Consortium Institution Progress ──
        $institutionProgress = [];
        foreach ($masterList as $instName => $meta) {
            $users = $groupedCMIs->get($instName, collect());
            $uIds  = $users->pluck('id')->all();

            $sub = null;
            if ($uIds) {
                $sub = ReportSubmission::where('reporting_year', $year)
                    ->whereIn('user_id', $uIds)
                    ->orderByDesc('submitted_at')
                    ->first();
            }
            $subStatus = $sub?->status;

            $distinctTables = [];
            $hasAnyDraft = false;
            foreach ($uIds as $uid) {
                $userTables = $tablesByUser[$uid] ?? [];
                foreach ($userTables as $tr) {
                    $cleanKey = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $tr->table_no));
                    if (in_array($tr->status, ['done', 'submitted', 'accepted'], true)) {
                        $distinctTables[$cleanKey] = true;
                    }
                    if ($tr->status === 'draft' || !empty($tr->rows_json) || !empty($tr->meta_json)) {
                        $hasAnyDraft = true;
                    }
                }
            }

            $tablesDone = count($distinctTables);
            $pct = $TOTAL_TABLES > 0 ? round(($tablesDone / $TOTAL_TABLES) * 100) : 0;

            if ($subStatus === 'accepted') {
                $status = 'Accepted';
            } elseif ($subStatus === 'submitted') {
                $status = 'Submitted';
            } elseif ($subStatus === 'returned') {
                $status = 'Returned';
            } elseif ($tablesDone > 0 || $hasAnyDraft || $subStatus === 'in-progress') {
                $status = 'In Progress';
            } else {
                $status = 'Not Started';
            }

            $encoderNames = $users->pluck('name')->filter()->join(', ');

            $institutionProgress[] = [
                'institution'  => $instName,
                'short'        => $meta['short'] ?? '',
                'encoder'      => $encoderNames ?: '—',
                'tables_done'  => $tablesDone,
                'total_tables' => $TOTAL_TABLES,
                'pct'          => $pct,
                'status'       => $status,
                'submitted_at' => $sub?->submitted_at ? $sub->submitted_at->toDateTimeString() : null,
            ];
        }

        // Sort by progress percentage descending, then alphabetically
        usort($institutionProgress, function ($a, $b) {
            if ($b['pct'] !== $a['pct']) {
                return $b['pct'] <=> $a['pct'];
            }
            return strcmp($a['institution'], $b['institution']);
        });

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

        $recentActivity = ActivityLog::with('user')
            ->where('description', 'not like', '%Submitted CY%')
            ->where('description', 'not like', '%submitted%table%')
            ->where('description', 'not like', '%Submitted table%')
            ->orderByDesc('created_at')
            ->take(10)
            ->get()
            ->map(fn($al) => [
                'description' => $al->description,
                'created_at'  => $al->created_at ? $al->created_at->format('Y-m-d\TH:i:s+08:00') : null,
                'actor'       => $al->user ? $al->user->name : 'System',
            ]);

        $pendingApprovals = User::where('status', 'pending')->whereIn('role', ['cmi', 'viewer', 'executive'])->count();

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
        $monthlySubmitted = [];
        $monthlyAccepted  = [];
        $monthlyReturned  = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthlySubmitted[$m] = 0;
            $monthlyAccepted[$m]  = 0;
            $monthlyReturned[$m]  = 0;
        }

        $monthlySubQuery = ReportSubmission::where('reporting_year', $year)
            ->whereNotNull('submitted_at')
            ->whereYear('submitted_at', $year)
            ->selectRaw('MONTH(submitted_at) as month, status, COUNT(*) as cnt')
            ->groupBy('month', 'status')
            ->get();

        $subCountTotal = 0;
        foreach ($monthlySubQuery as $row) {
            $m  = (int) $row->month;
            $st = strtolower($row->status);
            $c  = (int) $row->cnt;
            $subCountTotal += $c;
            if ($st === 'accepted') {
                $monthlyAccepted[$m] += $c;
            } elseif ($st === 'returned') {
                $monthlyReturned[$m] += $c;
            } else {
                $monthlySubmitted[$m] += $c;
            }
        }

        if ($subCountTotal === 0) {
            $tableMonthlyQuery = ReportTable::where('reporting_year', $year)
                ->whereIn('status', ['submitted', 'accepted', 'returned'])
                ->selectRaw('MONTH(updated_at) as month, status, COUNT(DISTINCT user_id) as cnt')
                ->groupBy('month', 'status')
                ->get();
            foreach ($tableMonthlyQuery as $row) {
                $m  = (int) $row->month;
                $st = strtolower($row->status);
                $c  = (int) $row->cnt;
                if ($st === 'accepted') {
                    $monthlyAccepted[$m] += $c;
                } elseif ($st === 'returned') {
                    $monthlyReturned[$m] += $c;
                } elseif ($st === 'submitted') {
                    $monthlySubmitted[$m] += $c;
                }
            }
        }

        $monthlyLabels    = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $mSubmittedVals   = array_values($monthlySubmitted);
        $mAcceptedVals    = array_values($monthlyAccepted);
        $mReturnedVals    = array_values($monthlyReturned);
        $monthlyValues    = array_map(fn($i) => $mSubmittedVals[$i] + $mAcceptedVals[$i] + $mReturnedVals[$i], range(0, 11));

        // ── 3. QUARTERLY TREND (Q1–Q4 of selected year) ──
        $quarterlySubmitted = [
            ($monthlySubmitted[1] + $monthlySubmitted[2] + $monthlySubmitted[3]),
            ($monthlySubmitted[4] + $monthlySubmitted[5] + $monthlySubmitted[6]),
            ($monthlySubmitted[7] + $monthlySubmitted[8] + $monthlySubmitted[9]),
            ($monthlySubmitted[10] + $monthlySubmitted[11] + $monthlySubmitted[12]),
        ];
        $quarterlyAccepted = [
            ($monthlyAccepted[1] + $monthlyAccepted[2] + $monthlyAccepted[3]),
            ($monthlyAccepted[4] + $monthlyAccepted[5] + $monthlyAccepted[6]),
            ($monthlyAccepted[7] + $monthlyAccepted[8] + $monthlyAccepted[9]),
            ($monthlyAccepted[10] + $monthlyAccepted[11] + $monthlyAccepted[12]),
        ];
        $quarterlyReturned = [
            ($monthlyReturned[1] + $monthlyReturned[2] + $monthlyReturned[3]),
            ($monthlyReturned[4] + $monthlyReturned[5] + $monthlyReturned[6]),
            ($monthlyReturned[7] + $monthlyReturned[8] + $monthlyReturned[9]),
            ($monthlyReturned[10] + $monthlyReturned[11] + $monthlyReturned[12]),
        ];
        $quarterlyLabels = ['Q1 (Jan–Mar)', 'Q2 (Apr–Jun)', 'Q3 (Jul–Sep)', 'Q4 (Oct–Dec)'];
        $quarterlyValues = array_map(fn($i) => $quarterlySubmitted[$i] + $quarterlyAccepted[$i] + $quarterlyReturned[$i], range(0, 3));

        // ── 4. ANNUAL TREND (Last 5 Years) ──
        $currentYear = (int) ($year ?: date('Y'));
        $annualLabels    = [];
        $annualSubmitted = [];
        $annualAccepted  = [];
        $annualReturned  = [];
        for ($y = $currentYear - 4; $y <= $currentYear; $y++) {
            $annualLabels[] = (string) $y;
            $annualSubmitted[$y] = 0;
            $annualAccepted[$y]  = 0;
            $annualReturned[$y]  = 0;
        }

        $annualData = ReportSubmission::whereNotNull('submitted_at')
            ->whereBetween('reporting_year', [$currentYear - 4, $currentYear])
            ->selectRaw('reporting_year, status, COUNT(*) as cnt')
            ->groupBy('reporting_year', 'status')
            ->get();

        $annualTotal = 0;
        foreach ($annualData as $row) {
            $ry = (int) $row->reporting_year;
            $st = strtolower($row->status);
            $c  = (int) $row->cnt;
            $annualTotal += $c;
            if (isset($annualSubmitted[$ry])) {
                if ($st === 'accepted') {
                    $annualAccepted[$ry] += $c;
                } elseif ($st === 'returned') {
                    $annualReturned[$ry] += $c;
                } else {
                    $annualSubmitted[$ry] += $c;
                }
            }
        }

        if ($annualTotal === 0) {
            $tableAnnual = ReportTable::whereIn('status', ['submitted', 'accepted', 'returned'])
                ->whereBetween('reporting_year', [$currentYear - 4, $currentYear])
                ->selectRaw('reporting_year, status, COUNT(DISTINCT user_id) as cnt')
                ->groupBy('reporting_year', 'status')
                ->get();
            foreach ($tableAnnual as $row) {
                $ry = (int) $row->reporting_year;
                $st = strtolower($row->status);
                $c  = (int) $row->cnt;
                if (isset($annualSubmitted[$ry])) {
                    if ($st === 'accepted') {
                        $annualAccepted[$ry] += $c;
                    } elseif ($st === 'returned') {
                        $annualReturned[$ry] += $c;
                    } elseif ($st === 'submitted') {
                        $annualSubmitted[$ry] += $c;
                    }
                }
            }
        }

        $aSubmittedVals = array_values($annualSubmitted);
        $aAcceptedVals  = array_values($annualAccepted);
        $aReturnedVals  = array_values($annualReturned);
        $annualValues   = array_map(fn($i) => $aSubmittedVals[$i] + $aAcceptedVals[$i] + $aReturnedVals[$i], range(0, count($annualLabels) - 1));

        return response()->json([
            'ok'   => true,
            'year' => $year,
            'stats' => [
                'total_cmis'        => count($masterList) ?: $totalCMIs,
                'submitted'         => $instSubmitted,
                'in_progress'       => $instInProgress,
                'not_started'       => $instNotStarted,
                'returned'          => $instReturned,
                'accepted'          => $instAccepted,
                'table_submitted'   => $tableSubmitted,
                'table_in_progress' => $tableInProgress,
                'table_not_started' => $tableNotStarted,
                'table_returned'    => $tableReturned,
                'table_accepted'    => $tableAccepted,
                'cmi_submitted'     => $instSubmitted,
                'cmi_in_progress'   => $instInProgress,
                'cmi_not_started'   => $instNotStarted,
                'cmi_returned'      => $instReturned,
                'cmi_accepted'      => $instAccepted,
                'pending_approvals' => $pendingApprovals,
            ],
            'section_progress' => $sectionProgress,
            'recent_activity'  => $recentActivity,
            'cmi_list'             => $institutionProgress,
            'cmi_status_list'      => $institutionProgress,
            'institution_progress' => $institutionProgress,
            'trend_labels'        => $monthlyLabels,
            'trend_values'        => $monthlyValues,
            'monthly_labels'      => $monthlyLabels,
            'monthly_values'      => $monthlyValues,
            'monthly_submitted'   => $mSubmittedVals,
            'monthly_accepted'    => $mAcceptedVals,
            'monthly_returned'    => $mReturnedVals,
            'quarterly_labels'    => $quarterlyLabels,
            'quarterly_values'    => $quarterlyValues,
            'quarterly_submitted' => $quarterlySubmitted,
            'quarterly_accepted'  => $quarterlyAccepted,
            'quarterly_returned'  => $quarterlyReturned,
            'annual_labels'       => $annualLabels,
            'annual_values'       => $annualValues,
            'annual_submitted'    => $aSubmittedVals,
            'annual_accepted'     => $aAcceptedVals,
            'annual_returned'     => $aReturnedVals,
        ]);
    }
}
