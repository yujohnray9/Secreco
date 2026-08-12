<?php

namespace App\Http\Controllers\Pta;

use App\Http\Controllers\Controller;
use App\Models\FormatTemplate;
use App\Models\Notification;
use App\Models\ReportTable;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FormatController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $year = (int) ($request->input('year') ?? date('Y'));
        $totalCmi = User::where('role', 'cmi')->where('status', 'active')->count();

        $templates = FormatTemplate::where('year', $year)
            ->orderBy('sort_order', 'asc')
            ->orderBy('table_no', 'asc')
            ->get();

        $templates = $templates->map(function ($ft) use ($year, $totalCmi) {
            $subCount = ReportTable::where('reporting_year', $year)
                ->where('table_no', $ft->table_no)
                ->whereIn('status', ['done', 'draft'])
                ->distinct('user_id')
                ->count('user_id');

            $array = $ft->toArray();
            $array['submission_count'] = $subCount;
            $array['total_cmi']        = $totalCmi;
            return $array;
        });

        $activeYear = (int) (SystemSetting::where('key', 'active_year')->value('value') ?? date('Y'));
        $years = FormatTemplate::distinct()->where('year', '>=', 2025)->orderByDesc('year')->pluck('year')->all();
        if (empty($years)) {
            $years = [2025];
        }
        if (!in_array((int) date('Y'), $years, true) && (int) date('Y') >= 2025) {
            $years[] = (int) date('Y');
            rsort($years);
        }

        return response()->json([
            'ok'          => true,
            'templates'   => $templates,
            'years'       => array_values(array_unique($years)),
            'year'        => $year,
            'active_year' => $activeYear,
        ]);
    }

    public function save(Request $request): JsonResponse
    {
        $input  = $request->json()->all() ?: $request->all();
        $action = trim($input['action'] ?? '');
        $userId = (int) (Auth::id() ?? session('user_id'));

        switch ($action) {
            case 'add':
                $year      = (int) ($input['year'] ?? 0);
                $tableNo   = strtoupper(trim($input['table_no'] ?? ''));
                $title     = trim($input['title'] ?? '');
                $section   = trim($input['section'] ?? '');
                $required  = !empty($input['is_required']) ? 1 : 0;
                $colsJson  = $input['columns_json'] ?? null;

                if (!$year || !$tableNo || !$title || !$section) {
                    return response()->json(['ok' => false, 'message' => 'Missing required fields.']);
                }

                $nextSort = (int) FormatTemplate::where('year', $year)->max('sort_order') + 1;

                $colsEncoded = null;
                if ($colsJson !== null) {
                    $decoded = is_array($colsJson) ? $colsJson : json_decode($colsJson, true);
                    $colsEncoded = ($decoded !== null) ? $decoded : null;
                }

                $yearStatus = FormatTemplate::where('year', $year)->value('status') ?? 'draft';

                try {
                    FormatTemplate::create([
                        'year'         => $year,
                        'table_no'     => $tableNo,
                        'title'        => $title,
                        'section'      => $section,
                        'is_required'  => $required,
                        'sort_order'   => $nextSort,
                        'status'       => $yearStatus,
                        'created_by'   => $userId,
                        'columns_json' => $colsEncoded,
                    ]);
                    return response()->json(['ok' => true, 'message' => "Table {$tableNo} added."]);
                } catch (\Throwable $e) {
                    return response()->json(['ok' => false, 'message' => 'Table number already exists for this year.']);
                }

            case 'edit':
                $id       = (int) ($input['id'] ?? 0);
                $tableNo  = strtoupper(trim($input['table_no'] ?? ''));
                $title    = trim($input['title'] ?? '');
                $section  = trim($input['section'] ?? '');
                $required = !empty($input['is_required']) ? 1 : 0;
                $colsJson = $input['columns_json'] ?? null;

                if (!$id || !$tableNo || !$title || !$section) {
                    return response()->json(['ok' => false, 'message' => 'Missing fields.']);
                }

                $ft = FormatTemplate::find($id);
                if (!$ft) {
                    return response()->json(['ok' => false, 'message' => 'Template not found.']);
                }

                $colsEncoded = null;
                if ($colsJson !== null) {
                    $decoded = is_array($colsJson) ? $colsJson : json_decode($colsJson, true);
                    $colsEncoded = ($decoded !== null) ? $decoded : null;
                }

                try {
                    $ft->update([
                        'table_no'     => $tableNo,
                        'title'        => $title,
                        'section'      => $section,
                        'is_required'  => $required,
                        'columns_json' => $colsEncoded,
                    ]);
                    return response()->json(['ok' => true, 'message' => "Table {$tableNo} updated."]);
                } catch (\Throwable $e) {
                    return response()->json(['ok' => false, 'message' => 'Table number already exists for this year.']);
                }

            case 'delete':
                $id = (int) ($input['id'] ?? 0);
                if (!$id) {
                    return response()->json(['ok' => false, 'message' => 'Invalid ID.']);
                }

                $ft = FormatTemplate::find($id);
                if (!$ft) {
                    return response()->json(['ok' => false, 'message' => 'Template not found.']);
                }

                $hasData = ReportTable::where('table_no', $ft->table_no)->where('reporting_year', $ft->year)->count();
                if ($hasData > 0) {
                    return response()->json(['ok' => false, 'message' => "Cannot delete {$ft->table_no} — submissions exist. Archive the year instead."]);
                }

                $tableNo = $ft->table_no;
                $ft->delete();
                return response()->json(['ok' => true, 'message' => "Table {$tableNo} removed."]);

            case 'activate':
                $year = (int) ($input['year'] ?? 0);
                if (!$year) {
                    return response()->json(['ok' => false, 'message' => 'Invalid year.']);
                }

                FormatTemplate::where('status', 'active')->update(['status' => 'archived']);
                FormatTemplate::where('year', $year)->update(['status' => 'active']);

                SystemSetting::updateOrCreate(
                    ['key' => 'active_year'],
                    ['value' => (string) $year, 'updated_by' => $userId]
                );

                $cmiUsers = User::where('role', 'cmi')->where('status', 'active')->pluck('id');
                foreach ($cmiUsers as $cmiId) {
                    Notification::create([
                        'user_id'      => $cmiId,
                        'role'         => 'cmi',
                        'type'         => 'year_activation',
                        'icon'         => '🎉',
                        'color'        => 'green',
                        'message'      => "CY {$year} is now active! You may now start filling up and submitting your annual accomplishment report.",
                        'action_url'   => '/dashboard/cmi/fillup',
                        'action_label' => 'Start Filling Up',
                        'is_read'      => false,
                        'created_at'   => now(),
                    ]);
                }

                return response()->json(['ok' => true, 'message' => "CY {$year} is now active. Previous year archived."]);

            case 'clone':
                $fromYear = (int) ($input['from_year'] ?? 0);
                $toYear   = (int) ($input['to_year'] ?? 0);
                if (!$fromYear || !$toYear || $fromYear === $toYear) {
                    return response()->json(['ok' => false, 'message' => 'Invalid years.']);
                }

                if (FormatTemplate::where('year', $toYear)->exists()) {
                    return response()->json(['ok' => false, 'message' => "CY {$toYear} format already exists."]);
                }

                $sourceRows = FormatTemplate::where('year', $fromYear)->orderBy('sort_order')->get();
                $count = 0;
                foreach ($sourceRows as $r) {
                    FormatTemplate::create([
                        'year'        => $toYear,
                        'table_no'    => $r->table_no,
                        'title'       => $r->title,
                        'section'     => $r->section,
                        'is_required' => $r->is_required,
                        'sort_order'  => $r->sort_order,
                        'status'      => 'draft',
                        'created_by'  => $userId,
                    ]);
                    $count++;
                }

                return response()->json(['ok' => true, 'message' => "CY {$toYear} draft created with {$count} tables cloned from CY {$fromYear}."]);

            default:
                return response()->json(['ok' => false, 'message' => "Unknown action: {$action}"]);
        }
    }
}
