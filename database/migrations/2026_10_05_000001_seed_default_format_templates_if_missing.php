<?php

use Illuminate\Database\Migrations\Migration;
use App\Http\Controllers\Pta\FormatController;
use App\Models\FormatTemplate;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (FormatTemplate::count() === 0) {
            FormatController::seedDefaultTemplates(2025);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op to preserve format templates
    }
};
