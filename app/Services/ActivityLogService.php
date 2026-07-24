<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Log;
use Throwable;

class ActivityLogService
{
    public static function log(?int $userId, string $description): void
    {
        try {
            ActivityLog::create([
                'user_id'     => $userId,
                'description' => $description,
                'created_at'  => now(),
            ]);
        } catch (Throwable $e) {
            Log::error('ActivityLogService error: ' . $e->getMessage());
        }
    }
}
