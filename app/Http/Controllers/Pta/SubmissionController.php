<?php

namespace App\Http\Controllers\Pta;

use App\Http\Controllers\Controller;
use App\Models\CorrectionRequest;
use App\Models\Notification;
use App\Models\ReportSubmission;
use App\Models\ReportTable;
use App\Models\ReportTableDoc;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubmissionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $year   = (int) ($request->input('year') ?? date('Y'));
        $status = $request->input('status');

        $query = ReportTable::where('reporting_year', $year)
            ->whereHas('user', function ($q) {
                $q->where('role', 'cmi')->where('status', 'active');
            })
            ->with('user');

        if ($status) {
            $query->where('status', $status);
        } else {
            $query->whereIn('status', ['done', 'submitted', 'accepted', 'returned', 'deleted']);
        }

        $tables = $query->orderBy(User::select('institution')->whereColumn('users.id', 'report_tables.user_id'))
            ->orderBy('table_no')
            ->get();

        $userIds = $tables->pluck('user_id')->unique()->filter()->values()->all();

        $docsByUserTable = [];
        if ($userIds) {
            $docs = ReportTableDoc::where('reporting_year', $year)
                ->whereIn('user_id', $userIds)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            foreach ($docs as $doc) {
                $key = $doc->user_id . '_' . $doc->table_no;
                $docsByUserTable[$key][] = [
                    'file_path' => $doc->file_path,
                    'caption'   => $doc->caption ?? '',
                ];
            }
        }

        $corrByUserTable = [];
        if ($userIds) {
            $corrections = CorrectionRequest::where('reporting_year', $year)
                ->where('status', 'open')
                ->whereIn('cmi_user_id', $userIds)
                ->get();

            foreach ($corrections as $cr) {
                $key = $cr->cmi_user_id . '_' . $cr->table_no;
                $corrByUserTable[$key] = true;
            }
        }

        $rows = [];
        foreach ($tables as $t) {
            $key = $t->user_id . '_' . $t->table_no;
            $rows[] = [
                'cmi_user_id'         => (int) $t->user_id,
                'institution'         => $t->user?->institution ?? '—',
                'encoder'             => $t->user?->name,
                'table_no'            => $t->table_no,
                'table_status'        => $t->status,
                'updated_at'          => $t->updated_at ? $t->updated_at->toDateTimeString() : null,
                'meta'                => $t->meta_json,
                'rows'                => $t->rows_json ?? [],
                'docs'                => $docsByUserTable[$key] ?? [],
                'has_open_correction' => isset($corrByUserTable[$key]),
            ];
        }

        return response()->json(['ok' => true, 'year' => $year, 'rows' => $rows]);
    }

    public function accept(Request $request): JsonResponse
    {
        $submissionId = (int) $request->input('submission_id', 0);
        $id           = (int) $request->input('id', 0);
        $cmiUserId    = (int) $request->input('cmi_user_id', 0);
        $tableNo      = strtoupper(trim($request->input('table_no', '')));
        $year         = (int) ($request->input('year') ?? date('Y'));

        $sub = null;

        if ($submissionId > 0 || $id > 0) {
            $targetId = $submissionId ?: $id;
            $sub = ReportSubmission::find($targetId);
        }

        if ($cmiUserId > 0) {
            ReportTable::where('user_id', $cmiUserId)
                ->where('reporting_year', $year)
                ->where('table_no', $tableNo)
                ->update(['status' => 'accepted', 'updated_at' => now()]);

            if (!$sub) {
                $sub = ReportSubmission::where('user_id', $cmiUserId)
                    ->where('reporting_year', $year)
                    ->first();
            }
        }

        if ($sub) {
            $sub->update(['status' => 'accepted', 'remarks' => null]);
            $cmiUserId = $cmiUserId ?: $sub->user_id;
        }

        $cmiUser = User::find($cmiUserId);
        $inst = $cmiUser?->institution ?: ($cmiUser?->name ?? 'CMI User');

        if ($cmiUser) {
            Notification::create([
                'user_id'      => $cmiUser->id,
                'role'         => 'cmi',
                'type'         => 'submitted',
                'icon'         => '✅',
                'color'        => 'green',
                'message'      => "Your CY {$year} Table {$tableNo} submission has been accepted by PTA. Great work!",
                'action_url'   => '/dashboard/cmi/submissions',
                'action_label' => 'View',
                'is_read'      => false,
                'created_at'   => now(),
            ]);
        }

        $ptaId = Auth::id() ?? session('user_id');
        ActivityLogService::log($ptaId, "Accepted submission Table {$tableNo} from {$inst} (CY {$year})");

        return response()->json(['ok' => true, 'message' => "Table {$tableNo} submission from {$inst} accepted successfully."]);
    }

    public function requestCorrection(Request $request): JsonResponse
    {
        $cmiUserId = (int) $request->input('cmi_user_id', 0);
        $tableNo   = strtoupper(trim($request->input('table_no', '')));
        $year      = (int) ($request->input('year') ?? date('Y'));
        $reason    = trim($request->input('reason', ''));
        $ptaUserId = (int) (Auth::id() ?? session('user_id'));

        if (!$cmiUserId || !$tableNo || !$reason) {
            return response()->json(['ok' => false, 'error' => 'Missing required fields.']);
        }
        if (mb_strlen($reason) > 2000) {
            return response()->json(['ok' => false, 'error' => 'Reason is too long (max 2000 characters).']);
        }

        $check = User::where('id', $cmiUserId)->where('role', 'cmi')->where('status', 'active')->exists();
        if (!$check) {
            return response()->json(['ok' => false, 'error' => 'CMI user not found.']);
        }

        ReportTable::where('user_id', $cmiUserId)
            ->where('reporting_year', $year)
            ->where('table_no', $tableNo)
            ->update(['status' => 'returned', 'updated_at' => now()]);

        CorrectionRequest::create([
            'cmi_user_id'    => $cmiUserId,
            'pta_user_id'    => $ptaUserId,
            'reporting_year' => $year,
            'table_no'       => $tableNo,
            'reason'         => $reason,
            'status'         => 'open',
            'created_at'     => now(),
        ]);

        $ptaUser  = User::find($ptaUserId);
        $ptaName  = $ptaUser?->name ?? 'PTA';
        $notifMsg = "{$tableNo} has been flagged for correction by {$ptaName}: \"{$reason}\"";

        Notification::create([
            'user_id'      => $cmiUserId,
            'role'         => 'cmi',
            'type'         => 'correction',
            'icon'         => '🔴',
            'color'        => 'red',
            'message'      => $notifMsg,
            'action_url'   => '/dashboard/cmi/fillup',
            'action_label' => "Fix {$tableNo}",
            'is_read'      => false,
            'created_at'   => now(),
        ]);

        ReportSubmission::where('user_id', $cmiUserId)
            ->where('reporting_year', $year)
            ->whereIn('status', ['pending', 'in-progress', 'submitted'])
            ->orderByDesc('submitted_at')
            ->first()
            ?->update(['status' => 'returned', 'remarks' => $reason]);

        ActivityLogService::log($ptaUserId, "Correction requested for {$tableNo} — CMI user #{$cmiUserId}: \"{$reason}\"");

        return response()->json(['ok' => true]);
    }

    public function delete(Request $request): JsonResponse
    {
        $cmiUserId = (int) $request->input('cmi_user_id', 0);
        $tableNo   = strtoupper(trim($request->input('table_no', '')));
        $year      = (int) ($request->input('year') ?? date('Y'));
        $ptaUserId = (int) (Auth::id() ?? session('user_id'));

        if (!$cmiUserId || !$tableNo) {
            return response()->json(['ok' => false, 'error' => 'Missing required fields.']);
        }

        ReportTable::where('user_id', $cmiUserId)
            ->where('reporting_year', $year)
            ->where('table_no', $tableNo)
            ->update(['status' => 'deleted', 'updated_at' => now()]);

        $cmiUser = User::find($cmiUserId);
        $inst    = $cmiUser?->institution ?: ($cmiUser?->name ?? 'CMI User');

        ActivityLogService::log($ptaUserId, "Deleted submission {$tableNo} from {$inst} (CY {$year})");

        return response()->json(['ok' => true, 'message' => "Submission {$tableNo} from {$inst} deleted."]);
    }
}
