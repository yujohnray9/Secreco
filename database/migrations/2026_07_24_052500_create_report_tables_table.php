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
        if (!Schema::hasTable('report_tables')) {
            Schema::create('report_tables', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('user_id');
                $table->year('reporting_year');
                $table->string('table_no', 10);
                $table->longText('meta_json')->nullable();
                $table->longText('rows_json')->nullable();
                $table->enum('status', ['not-started', 'draft', 'done', 'error'])->default('not-started');
                $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

                $table->unique(['user_id', 'reporting_year', 'table_no'], 'uq_user_year_table');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('report_tables');
    }
};
