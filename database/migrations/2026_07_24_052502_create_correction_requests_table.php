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
        if (!Schema::hasTable('correction_requests')) {
            Schema::create('correction_requests', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('cmi_user_id');
                $table->unsignedInteger('pta_user_id');
                $table->year('reporting_year');
                $table->string('table_no', 10);
                $table->text('reason');
                $table->enum('status', ['open', 'resolved'])->default('open');
                $table->dateTime('created_at')->useCurrent();
                $table->dateTime('resolved_at')->nullable();

                $table->index(['cmi_user_id', 'reporting_year'], 'idx_cr_cmi');
                $table->index(['cmi_user_id', 'reporting_year', 'table_no'], 'idx_cr_table');
                $table->foreign('cmi_user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('pta_user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('correction_requests');
    }
};
