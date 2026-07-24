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
        if (!Schema::hasTable('format_templates')) {
            Schema::create('format_templates', function (Blueprint $table) {
                $table->increments('id');
                $table->year('year');
                $table->string('table_no', 10);
                $table->string('title', 255);
                $table->string('section', 100);
                $table->boolean('is_required')->default(true);
                $table->text('columns_json')->nullable();
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->enum('status', ['draft', 'active', 'archived'])->default('draft');
                $table->unsignedInteger('created_by')->nullable();
                $table->dateTime('created_at')->useCurrent();
                $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

                $table->unique(['year', 'table_no'], 'uq_year_table');
                $table->index('year', 'idx_ft_year');
                $table->index('status', 'idx_ft_status');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('format_templates');
    }
};
