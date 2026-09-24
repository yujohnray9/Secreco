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

        $activeYear = (int) (
            FormatTemplate::where('status', 'active')->value('year')
            ?? SystemSetting::where('key', 'active_year')->value('value')
            ?? FormatTemplate::max('year')
            ?? date('Y')
        );
        $dbYears = FormatTemplate::distinct()->where('year', '>=', 2025)->pluck('year')->all();
        $settingRow = SystemSetting::where('key', 'format_years')->first();
        $savedYears = $settingRow ? (json_decode($settingRow->value, true) ?: []) : [];
        $years = array_values(array_unique(array_merge($dbYears, $savedYears)));
        rsort($years);
        if (empty($years)) {
            $years = [2026, 2025];
        }

        return response()->json([
            'ok'          => true,
            'templates'   => $templates,
            'years'       => $years,
            'year'        => $year,
            'active_year' => $activeYear,
        ]);
    }

    public function save(Request $request): JsonResponse
    {
        $input     = $request->json()->all() ?: $request->all();
        $action    = trim($input['action'] ?? '');
        $rawUserId = Auth::id() ?? session('user_id');
        $userId    = ($rawUserId && User::where('id', $rawUserId)->exists()) ? (int) $rawUserId : null;

        switch ($action) {
            case 'add':
                $year = (int) ($input['year'] ?? 0);
                if (!$year) {
                    return response()->json(['ok' => false, 'message' => 'Invalid year.']);
                }

                $yearStatus = FormatTemplate::where('year', $year)->value('status') ?? 'draft';
                $canonicalMap = [];
                foreach (config('secreco.all_tables', []) as $stTable) {
                    $canonicalMap[strtoupper($stTable)] = $stTable;
                }

                // Support both bulk tables array and single table object
                $rawTables = $input['tables'] ?? null;
                if (!is_array($rawTables) || empty($rawTables)) {
                    $rawTables = [[
                        'table_no'     => $input['table_no'] ?? '',
                        'title'        => $input['title'] ?? '',
                        'section'      => $input['section'] ?? '',
                        'is_required'  => $input['is_required'] ?? 1,
                        'is_locked'    => $input['is_locked'] ?? 0,
                        'columns_json' => $input['columns_json'] ?? null,
                    ]];
                }

                $addedCount  = 0;
                $lastTableNo = '';
                $errors      = [];

                foreach ($rawTables as $tItem) {
                    $rawTable = trim($tItem['table_no'] ?? '');
                    if (!$rawTable) continue;
                    $tableNo  = $canonicalMap[strtoupper($rawTable)] ?? strtoupper($rawTable);
                    $title    = trim($tItem['title'] ?? '');
                    $section  = trim($tItem['section'] ?? 'R&D Mgt. & Coord.');
                    $required = !empty($tItem['is_required']) ? 1 : 0;
                    $locked   = !empty($tItem['is_locked']) ? 1 : 0;
                    $colsJson = $tItem['columns_json'] ?? null;

                    if (!$title) {
                        $errors[] = "Table {$tableNo} is missing a title.";
                        continue;
                    }

                    if (FormatTemplate::where('year', $year)->where('table_no', $tableNo)->exists()) {
                        $errors[] = "Table {$tableNo} already exists for CY {$year}.";
                        continue;
                    }

                    $nextSort = (int) FormatTemplate::where('year', $year)->max('sort_order') + 1;
                    $colsEncoded = null;
                    if ($colsJson !== null) {
                        $decoded = is_array($colsJson) ? $colsJson : json_decode($colsJson, true);
                        $colsEncoded = ($decoded !== null) ? $decoded : null;
                    }

                    FormatTemplate::create([
                        'year'         => $year,
                        'table_no'     => $tableNo,
                        'title'        => $title,
                        'subtitle'     => null,
                        'section'      => $section,
                        'is_required'  => $required,
                        'is_locked'    => $locked,
                        'sort_order'   => $nextSort,
                        'status'       => $yearStatus,
                        'created_by'   => $userId,
                        'columns_json' => $colsEncoded,
                    ]);

                    $addedCount++;
                    $lastTableNo = $tableNo;
                }

                if ($addedCount === 0) {
                    $msg = !empty($errors) ? implode(' ', $errors) : 'No valid tables were added.';
                    return response()->json(['ok' => false, 'message' => $msg]);
                }

                $msg = $addedCount === 1
                    ? "Table {$lastTableNo} added successfully."
                    : "{$addedCount} tables added successfully.";
                if (!empty($errors)) {
                    $msg .= ' (' . implode(' ', $errors) . ')';
                }

                return response()->json(['ok' => true, 'message' => $msg, 'count' => $addedCount]);

            case 'edit':
                $id       = (int) ($input['id'] ?? 0);
                $rawTable = trim($input['table_no'] ?? '');
                $canonicalMap = [];
                foreach (config('secreco.all_tables', []) as $stTable) {
                    $canonicalMap[strtoupper($stTable)] = $stTable;
                }
                $tableNo  = $canonicalMap[strtoupper($rawTable)] ?? strtoupper($rawTable);
                $title    = trim($input['title'] ?? '');
                $section  = trim($input['section'] ?? '');
                $required = !empty($input['is_required']) ? 1 : 0;
                $locked   = !empty($input['is_locked']) ? 1 : 0;
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
                        'subtitle'     => null,
                        'section'      => $section,
                        'is_required'  => $required,
                        'is_locked'    => $locked,
                        'columns_json' => $colsEncoded,
                    ]);
                    return response()->json(['ok' => true, 'message' => "Table {$tableNo} updated."]);
                } catch (\Throwable $e) {
                    return response()->json(['ok' => false, 'message' => 'Table number already exists for this year.']);
                }

            case 'toggle_lock':
                $id = (int) ($input['id'] ?? 0);
                $ft = FormatTemplate::find($id);
                if (!$ft) {
                    return response()->json(['ok' => false, 'message' => 'Template not found.']);
                }
                $ft->is_locked = !$ft->is_locked;
                $ft->save();
                $stateText = $ft->is_locked ? "locked (PTA only)" : "unlocked for CMI";
                return response()->json([
                    'ok'        => true,
                    'is_locked' => (bool) $ft->is_locked,
                    'message'   => "Table {$ft->table_no} is now {$stateText}."
                ]);

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

                $templateCount = FormatTemplate::where('year', $year)->count();
                if ($templateCount === 0) {
                    return response()->json([
                        'ok'      => false,
                        'message' => "Cannot activate CY {$year} because it has 0 table templates. Please add table formats before activating."
                    ]);
                }

                FormatTemplate::where('status', 'active')->update(['status' => 'archived']);
                FormatTemplate::where('year', $year)->update(['status' => 'active']);

                SystemSetting::updateOrCreate(
                    ['key' => 'active_year'],
                    ['value' => (string) $year, 'updated_by' => $userId]
                );

                $cmiUsers = User::where('role', 'cmi')->where('status', 'active')->pluck('id');
                foreach ($cmiUsers as $cmiId) {
                    Notification::where('user_id', $cmiId)
                        ->where('type', 'year_activation')
                        ->where(function ($q) use ($year) {
                            $q->where('message', 'like', "%CY {$year}%")
                              ->orWhere('message', 'like', "%CY{$year}%");
                        })
                        ->delete();

                    Notification::create([
                        'user_id'      => $cmiId,
                        'role'         => 'cmi',
                        'type'         => 'year_activation',
                        'icon'         => '🎉',
                        'color'        => 'blue',
                        'message'      => "CY {$year} is now active — you may start filling up and submitting your annual accomplishment report.",
                        'action_url'   => "/dashboard/cmi/fillup?year={$year}",
                        'action_label' => "Start Filling Up",
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
                        'subtitle'    => $r->subtitle,
                        'section'     => $r->section,
                        'is_required' => $r->is_required,
                        'is_locked'   => $r->is_locked,
                        'sort_order'  => $r->sort_order,
                        'status'      => 'draft',
                        'created_by'  => $userId,
                    ]);
                    $count++;
                }

                // Register year
                $settingRow = SystemSetting::where('key', 'format_years')->first();
                $savedYears = $settingRow ? (json_decode($settingRow->value, true) ?: []) : [];
                $dbYears    = FormatTemplate::distinct()->where('year', '>=', 2025)->pluck('year')->all();
                $allYears   = array_values(array_unique(array_merge($dbYears, $savedYears, [$toYear])));
                SystemSetting::updateOrCreate(
                    ['key' => 'format_years'],
                    ['value' => json_encode($allYears), 'updated_by' => $userId, 'updated_at' => now()]
                );

                return response()->json(['ok' => true, 'message' => "CY {$toYear} draft created with {$count} tables cloned from CY {$fromYear}."]);

            case 'create_blank_year':
                $targetYear = (int) ($input['year'] ?? 0);
                if (!$targetYear || $targetYear < 2020 || $targetYear > 2100) {
                    return response()->json(['ok' => false, 'message' => 'Please enter a valid reporting year.']);
                }

                $clearExisting = !empty($input['clear_existing']);
                $hasExisting = FormatTemplate::where('year', $targetYear)->exists();

                if ($hasExisting && !$clearExisting) {
                    return response()->json([
                        'ok'     => false,
                        'exists' => true,
                        'message' => "CY {$targetYear} already has tables. Would you like to clear them to start empty?"
                    ]);
                }

                if ($hasExisting && $clearExisting) {
                    FormatTemplate::where('year', $targetYear)->delete();
                }

                // Register year in SystemSetting so it appears in tabs even with 0 tables
                $settingRow = SystemSetting::where('key', 'format_years')->first();
                $savedYears = $settingRow ? (json_decode($settingRow->value, true) ?: []) : [];
                $dbYears    = FormatTemplate::distinct()->where('year', '>=', 2025)->pluck('year')->all();
                $allYears   = array_values(array_unique(array_merge($dbYears, $savedYears, [$targetYear])));
                sort($allYears);

                SystemSetting::updateOrCreate(
                    ['key' => 'format_years'],
                    ['value' => json_encode($allYears), 'updated_by' => $userId, 'updated_at' => now()]
                );

                return response()->json([
                    'ok'      => true,
                    'message' => "CY {$targetYear} created as an empty reporting year. You can now add tables.",
                    'year'    => $targetYear,
                ]);

            default:
                return response()->json(['ok' => false, 'message' => "Unknown action: {$action}"]);
        }
    }
}
