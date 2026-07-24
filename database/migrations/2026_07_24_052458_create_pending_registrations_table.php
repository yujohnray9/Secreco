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
        if (!Schema::hasTable('pending_registrations')) {
            Schema::create('pending_registrations', function (Blueprint $table) {
                $table->increments('id');
                $table->string('first_name', 100);
                $table->string('last_name', 100);
                $table->string('email', 191)->unique();
                $table->string('password', 255);
                $table->enum('role', ['pta', 'cmi', 'viewer'])->default('viewer');
                $table->string('institution', 255)->nullable();
                $table->string('designation', 255)->nullable();
                $table->char('otp', 6);
                $table->dateTime('otp_expires_at');
                $table->timestamp('created_at')->useCurrent();
                $table->boolean('verified')->default(false);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_registrations');
    }
};
