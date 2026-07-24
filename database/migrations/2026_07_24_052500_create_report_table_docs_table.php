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
        if (!Schema::hasTable('report_table_docs')) {
            Schema::create('report_table_docs', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('user_id');
                $table->year('reporting_year');
                $table->string('table_no', 10);
                $table->string('file_path', 500);
                $table->string('caption', 500)->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->dateTime('uploaded_at')->useCurrent();

                $table->index(['user_id', 'reporting_year', 'table_no'], 'idx_doc_lookup');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('report_table_docs');
    }
};
