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
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('user_id')->nullable();
                $table->enum('role', ['pta', 'cmi', 'viewer'])->nullable();
                $table->string('type', 50);
                $table->string('icon', 10)->default('📋');
                $table->enum('color', ['red', 'yellow', 'green', 'blue'])->default('blue');
                $table->text('message');
                $table->string('action_url', 500)->nullable();
                $table->string('action_label', 100)->nullable();
                $table->boolean('is_read')->default(false);
                $table->dateTime('created_at')->useCurrent();

                $table->index('user_id', 'idx_notif_user');
                $table->index('role', 'idx_notif_role');
                $table->index(['user_id', 'is_read'], 'idx_notif_unread');
                $table->index('created_at', 'idx_notif_created');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
