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
        if (!Schema::hasTable('submissions')) {
            Schema::create('submissions', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('user_id');
                $table->string('institution', 255)->nullable();
                $table->string('region', 100)->nullable();
                $table->unsignedInteger('format_id')->nullable();
                $table->enum('status', ['pending', 'completed', 'non-compliant', 'returned'])->default('pending');
                $table->dateTime('submitted_at')->useCurrent();
                $table->year('year')->nullable();
                $table->year('reporting_year')->nullable();
                $table->tinyInteger('tables_filled')->default(0);
                $table->longText('form_data')->nullable();
                $table->text('admin_notes')->nullable();
                $table->dateTime('reviewed_at')->nullable();
                $table->unsignedInteger('reviewed_by')->nullable();

                $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
