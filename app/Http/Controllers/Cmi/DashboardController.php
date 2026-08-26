<?php

namespace App\Http\Controllers\Cmi;

use App\Http\Controllers\Controller;
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
            foreach ($templates as $t) {
                $sec = trim($t->section) ?: 'General';
                $sections[$sec][] = $t->table_no;
                $allTableKeys[] = $t->table_no;
            }
        } else {
            $sections = config('secreco.sections');
            $allTableKeys = config('secreco.all_tables', [
                'T1', 'T2a', 'T2b', 'T3', 'T4', 'T5', 'T6', 'T7a', 'T7b',
                'T8a', 'T8b', 'T9', 'T10', 'T11', 'T12', 'T13', 'T14',
                'T15', 'T16', 'T17', 'T18', 'T19', 'T20a', 'T20b',
            ]);
        }
        $tableLabels = config('secreco.table_labels');
        $totalRequired = count($allTableKeys);

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

        $correction = Submission::where('user_id', $userId)->where('status', 'returned')->count();
        $corrMeta = 'Check remarks';
        if ($correction > 0) {
            $latestCorr = Submission::where('user_id', $userId)->where('status', 'returned')->orderByDesc('submitted_at')->value('id');
            if ($latestCorr) {
                $corrMeta = 'Submission #' . $latestCorr . ' flagged';
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
        $actRows = ReportTable::where('user_id', $userId)->orderByDesc('updated_at')->take(10)->get();

        foreach ($actRows as $act) {
            $key   = strtoupper($act->table_no);
            $label = $tableLabels[$key] ?? ('Table ' . $act->table_no);
            $ts    = $act->updated_at ? $act->updated_at->toDateTimeString() : null;

            switch ($act->status) {
                case 'done':
                    $desc = 'You submitted <strong>' . $label . '</strong>';
                    $icon = 'submitted';
                    break;
                case 'draft':
                    $desc = 'You saved <strong>' . $label . '</strong> as draft';
                    $icon = 'draft';
                    break;
                case 'error':
                    $desc = '<strong>' . $label . '</strong> flagged for correction — review remarks';
                    $icon = 'flagged';
                    break;
                default:
                    $desc = 'You started filling up <strong>' . $label . '</strong>';
                    $icon = 'started';
            }

            $recentActivity[] = [
                'icon'      => $icon,
                'desc'      => $desc,
                'timestamp' => $ts,
            ];
        }

        $retSubs = Submission::where('user_id', $userId)->where('status', 'returned')->orderByDesc('submitted_at')->take(3)->get();
        foreach ($retSubs as $ret) {
            $recentActivity[] = [
                'icon'      => 'flagged',
                'desc'      => 'PTA returned <strong>Submission #' . $ret->id . '</strong> for correction — review remarks',
                'timestamp' => $ret->submitted_at ? $ret->submitted_at->toDateTimeString() : null,
            ];
        }

        usort($recentActivity, fn($a, $b) => strcmp($b['timestamp'] ?? '', $a['timestamp'] ?? ''));
        $recentActivity = array_slice($recentActivity, 0, 10);

        $deadline    = '2026-06-30';
        $deadlineTs  = strtotime($deadline);
        $todayTs     = strtotime(date('Y-m-d'));
        $daysLeft    = max(0, (int) ceil(($deadlineTs - $todayTs) / 86400));
        $deadlineFmt = date('M j, Y', $deadlineTs);

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
            'sectionProgress' => $sectionProgress,
            'recentActivity'  => $recentActivity,
        ]);
    }
}
