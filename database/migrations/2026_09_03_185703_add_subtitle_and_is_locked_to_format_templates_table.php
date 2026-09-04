<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('format_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('format_templates', 'subtitle')) {
                $table->string('subtitle', 255)->nullable()->after('title');
            }
            if (!Schema::hasColumn('format_templates', 'is_locked')) {
                $table->boolean('is_locked')->default(false)->after('is_required');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('format_templates', function (Blueprint $table) {
            if (Schema::hasColumn('format_templates', 'subtitle')) {
                $table->dropColumn('subtitle');
            }
            if (Schema::hasColumn('format_templates', 'is_locked')) {
                $table->dropColumn('is_locked');
            }
        });
    }
};
