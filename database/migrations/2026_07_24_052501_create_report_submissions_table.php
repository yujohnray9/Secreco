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
        if (!Schema::hasTable('report_submissions')) {
            Schema::create('report_submissions', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('user_id');
                $table->year('reporting_year');
                $table->dateTime('submitted_at')->nullable();
                $table->enum('status', ['pending', 'accepted', 'returned', 'in-progress', 'submitted'])->default('pending');
                $table->longText('snapshot_json');
                $table->text('remarks')->nullable();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('report_submissions');
    }
};
