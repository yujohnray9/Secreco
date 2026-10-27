<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            if (!Schema::hasColumn('activity_log', 'action')) {
                $table->string('action', 255)->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('activity_log', 'outcome')) {
                $table->string('outcome', 100)->nullable()->default('Success')->after('action');
            }
            if (!Schema::hasColumn('activity_log', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('outcome');
            }
            if (!Schema::hasColumn('activity_log', 'user_display')) {
                $table->string('user_display', 255)->nullable()->after('ip_address');
            }
        });
    }

    public function down(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            $cols = [];
            foreach (['action', 'outcome', 'ip_address', 'user_display'] as $col) {
                if (Schema::hasColumn('activity_log', $col)) {
                    $cols[] = $col;
                }
            }
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
