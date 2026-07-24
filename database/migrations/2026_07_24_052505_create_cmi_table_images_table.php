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
        if (!Schema::hasTable('cmi_table_images')) {
            Schema::create('cmi_table_images', function (Blueprint $table) {
                $table->increments('id');
                $table->string('table_no', 10);
                $table->string('file_path', 500);
                $table->string('caption', 500)->default('');
                $table->unsignedInteger('uploaded_by');
                $table->dateTime('uploaded_at')->useCurrent();

                $table->index('table_no', 'idx_table_no');
                $table->index('uploaded_by', 'idx_uploaded_by');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cmi_table_images');
    }
};
