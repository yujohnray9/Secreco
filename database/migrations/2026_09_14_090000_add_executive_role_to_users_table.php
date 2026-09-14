<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('pta', 'cmi', 'viewer', 'executive') NOT NULL DEFAULT 'viewer'");
        
        try {
            DB::statement("ALTER TABLE pending_registrations MODIFY COLUMN role ENUM('pta', 'cmi', 'viewer', 'executive') NOT NULL DEFAULT 'viewer'");
        } catch (\Throwable $e) {}

        try {
            DB::statement("ALTER TABLE notifications MODIFY COLUMN role ENUM('pta', 'cmi', 'viewer', 'executive') NULL");
        } catch (\Throwable $e) {}
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('pta', 'cmi', 'viewer') NOT NULL DEFAULT 'viewer'");
    }
};
