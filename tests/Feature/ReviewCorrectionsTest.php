<?php

namespace Tests\Feature;

use App\Http\Controllers\Auth\UserApprovalController;
use App\Http\Controllers\Pta\FormatController;
use App\Models\FormatTemplate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ReviewCorrectionsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Use only the relevant migrations: the full historical set contains MySQL-only ALTER statements.
        foreach ([
            '0001_01_01_000000_create_users_table.php',
            '2026_07_24_052503_create_format_templates_table.php',
            '2026_09_03_185703_add_subtitle_and_is_locked_to_format_templates_table.php',
        ] as $file) {
            (require database_path('migrations/' . $file))->up();
        }
        Mail::fake();
    }

    public function test_lock_changes_only_the_selected_year_and_preserves_columns(): void
    {
        $columns = ['titles' => [['title' => 'Agency reviews', 'columns' => []]]];
        $old = FormatTemplate::create(['year' => 2025, 'table_no' => 'T1', 'title' => 'Reviews', 'section' => 'R&D', 'columns_json' => $columns]);
        $current = FormatTemplate::create(['year' => 2026, 'table_no' => 'T1', 'title' => 'Reviews', 'section' => 'R&D', 'columns_json' => $columns]);
        $response = (new FormatController)->save(Request::create('/', 'POST', ['action' => 'toggle_lock', 'id' => $current->id, 'year' => 2026]));
        $this->assertTrue($response->getData(true)['ok']);
        $this->assertTrue($current->fresh()->is_locked);
        $this->assertFalse($old->fresh()->is_locked);
        $this->assertSame($columns, $current->fresh()->columns_json);
    }

    public function test_stale_year_cannot_change_another_years_lock(): void
    {
        $format = FormatTemplate::create(['year' => 2025, 'table_no' => 'T1', 'title' => 'Reviews', 'section' => 'R&D']);
        $response = (new FormatController)->save(Request::create('/', 'POST', ['action' => 'toggle_lock', 'id' => $format->id, 'year' => 2026]));
        $this->assertSame(422, $response->getStatusCode());
        $this->assertFalse($format->fresh()->is_locked);
    }

    public function test_second_representative_can_be_approved_and_reactivated(): void
    {
        $base = ['first_name' => 'Test', 'last_name' => 'Representative', 'password' => 'test-password', 'role' => 'cmi', 'institution' => 'Test Institution'];
        User::create($base + ['email' => 'first@example.test', 'status' => 'active']);
        $second = User::create($base + ['email' => 'second@example.test', 'status' => 'pending']);
        $controller = new UserApprovalController;
        $response = $controller->approveUser(Request::create('/', 'POST', ['user_id' => $second->id, 'action' => 'approve']));
        $this->assertTrue($response->getData(true)['success']);
        $this->assertSame('active', $second->fresh()->status);
        $second->update(['status' => 'inactive']);
        $response = $controller->toggleUserStatus(Request::create('/', 'POST', ['user_id' => $second->id, 'new_status' => 'active']));
        $this->assertTrue($response->getData(true)['success']);
        $this->assertSame('active', $second->fresh()->status);
    }
}
