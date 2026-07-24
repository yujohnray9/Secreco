<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class RateLimitService
{
    public function checkRateLimit(string $ip): ?string
    {
        $key = 'login_rl_' . md5($ip);
        $data = Cache::get($key, [
            'attempts'      => 0,
            'first_attempt' => time(),
            'locked_until'  => 0,
        ]);

        $now = time();
        $maxAttempts = config('secreco.rate_limit_max', 5);
        $window = config('secreco.rate_limit_window', 900);

        if (($now - $data['first_attempt']) >= $window) {
            $data = ['attempts' => 0, 'first_attempt' => $now, 'locked_until' => 0];
        }

        if ($data['locked_until'] > $now) {
            $wait = ceil(($data['locked_until'] - $now) / 60);
            return "Too many failed attempts. Please wait {$wait} minute(s) before trying again.";
        }

        return null;
    }

    public function recordFailure(string $ip): void
    {
        $key = 'login_rl_' . md5($ip);
        $now = time();
        $maxAttempts = config('secreco.rate_limit_max', 5);
        $window = config('secreco.rate_limit_window', 900);

        $data = Cache::get($key, [
            'attempts'      => 0,
            'first_attempt' => $now,
            'locked_until'  => 0,
        ]);

        if (($now - $data['first_attempt']) >= $window) {
            $data = ['attempts' => 0, 'first_attempt' => $now, 'locked_until' => 0];
        }

        $data['attempts']++;
        if ($data['attempts'] >= $maxAttempts) {
            $data['locked_until'] = $now + $window;
        }

        Cache::put($key, $data, $window);
    }

    public function resetRateLimit(string $ip): void
    {
        $key = 'login_rl_' . md5($ip);
        Cache::forget($key);
    }
}
