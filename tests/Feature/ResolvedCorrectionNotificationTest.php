<?php

namespace Tests\Feature;

use App\Http\Controllers\NotificationController;
use App\Models\CorrectionRequest;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Tests\TestCase;

class ResolvedCorrectionNotificationTest extends TestCase
{
    public function test_resolved_notice_disappears_but_open_other_year_and_new_returns_remain(): void
    {
        foreach ([
            '0001_01_01_000000_create_users_table.php',
            '2026_07_24_052502_create_notifications_table.php',
            '2026_07_24_052502_create_correction_requests_table.php',
        ] as $migration) {
            (require database_path('migrations/' . $migration))->up();
        }
        $cmi = User::create(['first_name' => 'Test', 'last_name' => 'CMI', 'email' => 'cmi@example.test', 'password' => 'password', 'role' => 'cmi', 'status' => 'active']);
        $pta = User::create(['first_name' => 'Test', 'last_name' => 'PTA', 'email' => 'pta@example.test', 'password' => 'password', 'role' => 'pta', 'status' => 'active']);
        $this->actingAs($cmi);
        $createReturn = function ($table, $year, $time) use ($cmi, $pta) {
            $correction = CorrectionRequest::create(['cmi_user_id' => $cmi->id, 'pta_user_id' => $pta->id, 'reporting_year' => $year, 'table_no' => $table, 'reason' => 'Please revise', 'status' => 'open', 'created_at' => $time]);
            $notice = Notification::create(['user_id' => $cmi->id, 'role' => 'cmi', 'type' => 'correction', 'message' => "Table {$table} was returned for revision by the PTA Admin", 'action_url' => "/dashboard/cmi/fillup?table={$table}&year={$year}", 'created_at' => $time]);
            return [$correction, $notice];
        };
        $inbox = fn () => (new NotificationController)->get(Request::create('/', 'GET', ['year' => 2026]))->getData(true);
        [$correction, $notice] = $createReturn('T11', 2026, '2026-10-01 10:00:00');
        [, $otherTable] = $createReturn('T1', 2026, '2026-10-01 10:00:00');
        [, $otherYear] = $createReturn('T11', 2025, '2026-10-01 10:00:00');
        $this->assertCount(3, $inbox()['notifications']);
        $notice->update(['is_read' => true]);
        $this->assertCount(3, $inbox()['notifications'], 'Reading a return must not dismiss it.');
        $correction->update(['status' => 'resolved', 'resolved_at' => '2026-10-02 10:00:00']);
        $data = $inbox();
        $this->assertEqualsCanonicalizing([$otherTable->id, $otherYear->id], array_column($data['notifications'], 'id'));
        $this->assertSame(2, $data['unread_count']);
        $this->assertNotNull($notice->fresh(), 'Keep historical records.');
        [, $newReturn] = $createReturn('T11', 2026, '2026-10-03 10:00:00');
        $data = $inbox();
        $this->assertContains($newReturn->id, array_column($data['notifications'], 'id'));
        $this->assertNotContains($notice->id, array_column($data['notifications'], 'id'));
        $this->assertSame(3, $data['unread_count']);
    }
}
