<?php

namespace App\Http\Controllers\Cmi;

use App\Http\Controllers\Controller;
use App\Models\CorrectionRequest;
use App\Models\ReportSubmission;
use App\Models\ReportTable;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function getData(Request $request): JsonResponse
    {
        $activeYear = (int) (\App\Models\SystemSetting::where('key', 'active_year')->value('value') ?? date('Y'));
        $reportingYear = (int) ($request->input('year') ?? $activeYear);

        $templates = \App\Models\FormatTemplate::where('year', $reportingYear)
            ->orderBy('sort_order', 'asc')
            ->orderBy('table_no', 'asc')
            ->get();

        if ($templates->isNotEmpty()) {
            $sections = [];
            $allTableKeys = [];
            $tableLabels = [];
            foreach ($templates as $t) {
                $tNo = $t->table_no;
                $cleanKey = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $tNo));
                $allTableKeys[] = $tNo;
                $sec = trim($t->section) ?: 'General';
                $sections[$sec][] = $tNo;
                $tableLabels[$cleanKey] = $t->title ?: ('Table ' . $tNo);
            }
            $totalRequired = count($allTableKeys);
        } else {
            $baseSections = config('secreco.sections', [
                'R&D Mgt. & Coord.'       => ['T1', 'T2a', 'T2b', 'T3', 'T4', 'T5', 'T6', 'T7a', 'T7b'],
                'Strategic R&D'           => ['T8a', 'T8b', 'T9'],
                'Results Utilization'     => ['T10', 'T11', 'T12', 'T13'],
                'Capability & Governance' => ['T14', 'T15', 'T16', 'T17', 'T18', 'T19'],
                'Policy Analysis'         => ['T20a', 'T20b'],
            ]);
            $sections = $baseSections;
            $allTableKeys = config('secreco.all_tables', [
                'T1', 'T2a', 'T2b', 'T3', 'T4', 'T5', 'T6', 'T7a', 'T7b',
                'T8a', 'T8b', 'T9', 'T10', 'T11', 'T12', 'T13', 'T14',
                'T15', 'T16', 'T17', 'T18', 'T19', 'T20a', 'T20b',
            ]);
            $tableLabels = config('secreco.table_labels', []);
            $totalRequired = count($allTableKeys);
        }

        $userId = Auth::id() ?? session('user_id');

        // Get institution-mate IDs (CMI users with the same institution)
        $me = User::find($userId);
        $mateIds = [];
        if ($me && !empty(trim((string) $me->institution))) {
            $mateIds = User::where('institution', $me->institution)
                ->where('id', '!=', $userId)
                ->where('role', '!=', 'pta')
                ->pluck('id')
                ->toArray();
        }
        $allUserIds = array_merge([$userId], $mateIds);

        // Build a merged tableMap: best status across all institution users
        $priority = ['accepted' => 4, 'done' => 3, 'submitted' => 3, 'draft' => 2, 'not-started' => 0];
        $allRows  = ReportTable::whereIn('user_id', $allUserIds)
            ->where('reporting_year', $reportingYear)
            ->get(['user_id', 'table_no', 'status', 'updated_at']);

        $tableMap = [];
        foreach ($allRows as $row) {
            $cleanKey = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $row->table_no));
            $curStatus = $tableMap[$cleanKey]['status'] ?? 'not-started';
            if (($priority[$row->status] ?? 0) >= ($priority[$curStatus] ?? 0)) {
                $tableMap[$cleanKey] = $row->toArray();
            }
        }

        $complete   = 0;
        $draft      = 0;
        $notStarted = 0;

        foreach ($allTableKeys as $t) {
            $cleanKey = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $t));
            $status   = $tableMap[$cleanKey]['status'] ?? 'not-started';
            if (in_array($status, ['done', 'submitted', 'accepted'], true)) {
                $complete++;
            } elseif ($status === 'draft') {
                $draft++;
            } else {
                $notStarted++;
            }
        }

        // Auto-resolve any open CorrectionRequest records for tables that have already been fixed/completed
        foreach ($allTableKeys as $tKey) {
            $cleanKey = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $tKey));
            $curStatus = $tableMap[$cleanKey]['status'] ?? 'not-started';
            if (in_array($curStatus, ['done', 'submitted', 'accepted'], true)) {
                $candidates = array_unique([$tKey, strtoupper($tKey), strtolower($tKey), $cleanKey]);
                CorrectionRequest::whereIn('cmi_user_id', $allUserIds)
                    ->where('reporting_year', $reportingYear)
                    ->whereIn('table_no', $candidates)
                    ->where('status', 'open')
                    ->update(['status' => 'resolved', 'resolved_at' => now()]);
            }
        }

        // A table is for correction ONLY if its actual current status is 'returned'
        $returnedTableCount = 0;
        $unfixedTables = [];
        foreach ($allTableKeys as $tKey) {
            $cleanKey = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $tKey));
            $curStatus = $tableMap[$cleanKey]['status'] ?? 'not-started';
            if ($curStatus === 'returned') {
                $returnedTableCount++;
                $unfixedTables[$cleanKey] = $tKey;
            }
        }

        $correction = $returnedTableCount;
        $corrMeta = 'Check remarks';
        if ($correction > 0) {
            $latestCorr = CorrectionRequest::whereIn('cmi_user_id', $allUserIds)
                ->where('reporting_year', $reportingYear)
                ->where('status', 'open')
                ->whereIn('table_no', array_values($unfixedTables))
                ->orderByDesc('id')
                ->first();
            if ($latestCorr) {
                $cKey   = strtoupper($latestCorr->table_no);
                $cLabel = $tableLabels[$cKey] ?? ('Table ' . $latestCorr->table_no);
                $corrMeta = $cLabel . ' flagged';
            }
        }

        // Build detailed list of corrections with remarks for modal (only unfixed tables)
        $correctionsList = [];
        if ($correction > 0) {
            $rawCorrRequests = CorrectionRequest::whereIn('cmi_user_id', $allUserIds)
                ->where('reporting_year', $reportingYear)
                ->where('status', 'open')
                ->orderByDesc('id')
                ->get();

            $processedTables = [];
            foreach ($rawCorrRequests as $cr) {
                $cKey = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $cr->table_no));
                if (!isset($unfixedTables[$cKey])) {
                    continue; // Skip if already fixed!
                }
                $processedTables[$cKey] = true;
                $correctionsList[] = [
                    'id'         => $cr->id,
                    'table_no'   => $cr->table_no,
                    'title'      => $tableLabels[$cKey] ?? ('Table ' . $cr->table_no),
                    'reason'     => $cr->reason,
                    'created_at' => $cr->created_at ? $cr->created_at->format('M d, Y h:i A') : null,
                ];
            }

            // Include any returned ReportTable without an explicit CorrectionRequest
            foreach ($allRows as $r) {
                if ($r->status === 'returned') {
                    $cKey = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $r->table_no));
                    if (isset($unfixedTables[$cKey]) && !isset($processedTables[$cKey])) {
                        $processedTables[$cKey] = true;
                        $correctionsList[] = [
                            'id'         => $r->id,
                            'table_no'   => $r->table_no,
                            'title'      => $tableLabels[$cKey] ?? ('Table ' . $r->table_no),
                            'reason'     => 'Table flagged for revision by PTA administrator.',
                            'created_at' => $r->updated_at ? $r->updated_at->format('M d, Y h:i A') : null,
                        ];
                    }
                }
            }
        }

        $sectionProgress = [];
        foreach ($sections as $sectionName => $tables) {
            $total    = count($tables);
            $done     = 0;
            $hasDraft = false;
            foreach ($tables as $t) {
                $cleanKey = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $t));
                $status   = $tableMap[$cleanKey]['status'] ?? 'not-started';
                if (in_array($status, ['done', 'submitted', 'accepted'], true)) {
                    $done++;
                } elseif ($status === 'draft') {
                    $hasDraft = true;
                }
            }
            $pct = $total > 0 ? (int) round(($done / $total) * 100) : 0;
            $sectionProgress[] = [
                'section'  => $sectionName,
                'done'     => $done,
                'total'    => $total,
                'pct'      => $pct,
                'hasDraft' => $hasDraft,
            ];
        }

        $recentActivity = [];
        $actRows = ReportTable::whereIn('user_id', $allUserIds)
            ->where('reporting_year', $reportingYear)
            ->orderByDesc('updated_at')
            ->take(10)
            ->get();

        foreach ($actRows as $act) {
            $key   = strtoupper($act->table_no);
            $label = $tableLabels[$key] ?? ('Table ' . $act->table_no);
            $ts    = $act->updated_at ? $act->updated_at->toDateTimeString() : null;

            switch ($act->status) {
                case 'submitted':
                    $desc = 'You submitted <strong>' . $label . '</strong>';
                    $icon = 'submitted';
                    break;
                case 'accepted':
                    $desc = '<strong>' . $label . '</strong> was accepted by PTA';
                    $icon = 'submitted';
                    break;
                case 'returned':
                case 'error':
                    $desc = '<strong>' . $label . '</strong> was returned for revision — review remarks';
                    $icon = 'flagged';
                    break;
                case 'draft':
                    $desc = 'You saved <strong>' . $label . '</strong> as draft';
                    $icon = 'draft';
                    break;
                case 'done':
                    $desc = 'You saved <strong>' . $label . '</strong>';
                    $icon = 'draft';
                    break;
                default:
                    $desc = 'You updated <strong>' . $label . '</strong>';
                    $icon = 'started';
            }

            $recentActivity[] = [
                'icon'      => $icon,
                'desc'      => $desc,
                'timestamp' => $ts,
            ];
        }

        $corrList = CorrectionRequest::whereIn('cmi_user_id', $allUserIds)
            ->where('reporting_year', $reportingYear)
            ->orderByDesc('created_at')
            ->take(5)
            ->get();
        foreach ($corrList as $cr) {
            $cKey   = strtoupper($cr->table_no);
            $cLabel = $tableLabels[$cKey] ?? ('Table ' . $cr->table_no);
            $recentActivity[] = [
                'icon'      => 'flagged',
                'desc'      => 'PTA returned <strong>' . $cLabel . '</strong> for correction' . ($cr->reason ? ': "' . e($cr->reason) . '"' : ''),
                'timestamp' => $cr->created_at ? $cr->created_at->toDateTimeString() : null,
            ];
        }

        usort($recentActivity, fn($a, $b) => strcmp($b['timestamp'] ?? '', $a['timestamp'] ?? ''));
        $recentActivity = array_slice($recentActivity, 0, 10);

        $deadline    = '2026-06-30';
        $deadlineTs  = strtotime($deadline);
        $todayTs     = strtotime(date('Y-m-d'));
        $daysLeft    = max(0, (int) ceil(($deadlineTs - $todayTs) / 86400));
        $deadlineFmt = date('M j, Y', $deadlineTs);

        // ── Comprehensive Consortium Institution Progress (All Institutions) ──
        $allCmiUsers = User::where('role', 'cmi')->where('status', 'active')->orderBy('institution')->get();
        $groupedCMIs = $allCmiUsers->groupBy('institution');

        $allInstTables = ReportTable::where('reporting_year', $reportingYear)
            ->whereHas('user', function ($q) {
                $q->where('role', 'cmi')->where('status', 'active');
            })->get();

        $allTablesByUser = [];
        foreach ($allInstTables as $row) {
            $allTablesByUser[$row->user_id][] = $row;
        }

        $allSubmissions = ReportSubmission::where('reporting_year', $reportingYear)
            ->orderByDesc('submitted_at')
            ->get();
        $subsByUser = [];
        foreach ($allSubmissions as $s) {
            $subsByUser[$s->user_id][] = $s;
        }

        $masterList = config('secreco.institutions', []);

        // Ensure any institution with active CMI users is in masterList
        foreach ($groupedCMIs as $instName => $users) {
            if (!empty(trim((string) $instName))) {
                $matched = false;
                foreach (array_keys($masterList) as $mKey) {
                    if (strcasecmp(trim($mKey), trim($instName)) === 0) {
                        $matched = true;
                        break;
                    }
                }
                if (!$matched) {
                    $masterList[$instName] = ['short' => '', 'type' => 'Member Institution'];
                }
            }
        }

        $TOTAL_TABLES = $totalRequired > 0 ? $totalRequired : 24;
        $institutionProgress = [];

        foreach ($masterList as $instName => $meta) {
            $users = $groupedCMIs->get($instName, collect());
            if ($users->isEmpty()) {
                foreach ($groupedCMIs as $gKey => $gUsers) {
                    if (strcasecmp(trim((string) $gKey), trim((string) $instName)) === 0) {
                        $users = $gUsers;
                        break;
                    }
                }
            }
            $uIds = $users->pluck('id')->all();

            $sub = null;
            if ($uIds) {
                foreach ($uIds as $uid) {
                    if (!empty($subsByUser[$uid])) {
                        $candidateSub = $subsByUser[$uid][0];
                        if (!$sub || ($candidateSub->submitted_at && (!$sub->submitted_at || $candidateSub->submitted_at > $sub->submitted_at))) {
                            $sub = $candidateSub;
                        }
                    }
                }
            }
            $subStatus = $sub?->status;

            $distinctTables = [];
            $hasAnyDraft = false;
            foreach ($uIds as $uid) {
                $userTables = $allTablesByUser[$uid] ?? [];
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

        return response()->json([
            'stats' => [
                'complete'       => $complete,
                'totalRequired'  => $totalRequired,
                'draft'          => $draft,
                'notStarted'     => $notStarted,
                'correction'     => $correction,
                'correctionMeta' => $corrMeta,
            ],
            'deadline' => [
                'date'     => $deadlineFmt,
                'daysLeft' => $daysLeft,
            ],
            'my_institution'       => $me?->institution,
            'institution_progress' => $institutionProgress,
            'cmi_list'             => $institutionProgress,
            'cmi_status_list'      => $institutionProgress,
            'sectionProgress'      => $sectionProgress,
            'recentActivity'       => $recentActivity,
            'corrections'          => $correctionsList,
        ]);
    }
}
