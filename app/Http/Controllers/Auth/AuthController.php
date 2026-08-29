<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PendingRegistration;
use App\Models\User;
use App\Services\MailService;
use App\Services\RateLimitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class AuthController extends Controller
{
    protected MailService $mailService;
    protected RateLimitService $rateLimitService;

    public function __construct(MailService $mailService, RateLimitService $rateLimitService)
    {
        $this->mailService = $mailService;
        $this->rateLimitService = $rateLimitService;
    }

    public function login(Request $request): JsonResponse
    {
        $ip = $request->ip();

        // Rate limiting check
        if ($rlMessage = $this->rateLimitService->checkRateLimit($ip)) {
            return response()->json(['success' => false, 'message' => $rlMessage]);
        }

        // reCAPTCHA verification
        $recaptchaToken = trim($request->input('g_recaptcha_response') ?? $request->input('g-recaptcha-response', ''));
        if (empty($recaptchaToken)) {
            return response()->json(['success' => false, 'message' => 'Please complete the reCAPTCHA challenge.']);
        }

        $secret = config('secreco.recaptcha_secret_key');
        $captchaValid = false;

        try {
            $response = Http::asForm()->timeout(10)->withoutVerifying()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret'   => $secret,
                'response' => $recaptchaToken,
                'remoteip' => $ip,
            ]);
            $result = $response->json();
            $captchaValid = !empty($result['success']);
        } catch (Throwable $e) {
            Log::error('reCAPTCHA verification failed: ' . $e->getMessage());
        }

        if (!$captchaValid) {
            $this->rateLimitService->recordFailure($ip);
            return response()->json(['success' => false, 'message' => 'reCAPTCHA verification failed. Please try again.']);
        }

        // Credential validation
        $email    = trim($request->input('email', ''));
        $password = $request->input('password', '');

        if (empty($email) || empty($password)) {
            return response()->json(['success' => false, 'message' => 'Email and password are required.']);
        }

        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            $this->rateLimitService->recordFailure($ip);
            return response()->json(['success' => false, 'message' => 'Invalid email or password.']);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Your Account is Deactivated. Please contact the Project technical assistant II for assistance.'
            ]);
        }

        // Success
        $this->rateLimitService->resetRateLimit($ip);

        Auth::login($user);
        $request->session()->regenerate();

        session([
            'user_id'    => $user->id,
            'user_name'  => $user->first_name . ' ' . $user->last_name,
            'user_email' => $user->email,
            'user_role'  => $user->role,
            'user_inst'  => $user->institution,
            'user_desig' => $user->designation,
            'user_photo' => $user->profile_picture,
        ]);

        $redirect = match ($user->role) {
            'pta'    => '/dashboard/pta',
            'cmi'    => '/dashboard/cmi',
            'viewer' => '/dashboard/viewer',
            default  => '/login',
        };

        return response()->json(['success' => true, 'redirect' => $redirect]);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['success' => true, 'message' => 'Logged out successfully.']);
    }

    public function register(Request $request): JsonResponse
    {
        $email = trim($request->input('email', ''));

        // Resend OTP shortcut
        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $existing = PendingRegistration::where('email', $email)->where('verified', false)->first();

            if ($existing) {
                $otp       = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                $expiresAt = now()->addMinutes(10);

                $existing->update([
                    'otp'            => $otp,
                    'otp_expires_at' => $expiresAt,
                    'created_at'     => now(),
                ]);

                $fullName = $existing->first_name . ' ' . $existing->last_name;
                if (!$this->mailService->sendOtpEmail($email, $fullName, $otp)) {
                    return response()->json(['success' => false, 'message' => 'Failed to send verification email. Please try again.']);
                }

                return response()->json(['success' => true, 'message' => 'OTP resent! Check your email.']);
            }
        }

        $firstName   = trim($request->input('first_name', ''));
        $lastName    = trim($request->input('last_name', ''));
        $password    = $request->input('password', '');
        $pwConfirm   = $request->input('password_confirm', '');
        $role        = $request->input('role', '');
        if ($role === 'guest') {
            $role = 'viewer';
        }
        $institution = trim($request->input('institution', ''));
        $designation = trim($request->input('designation', ''));

        $errors = [];
        if (empty($firstName)) $errors[] = 'First name is required.';
        if (empty($lastName)) $errors[] = 'Last name is required.';
        if (empty($email)) $errors[] = 'Email is required.';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format.';
        if (!in_array($role, ['pta', 'cmi', 'viewer'])) $errors[] = 'Please select a valid role.';

        if ($role === 'pta') {
            $ptaExists = User::where('role', 'pta')->exists();
            if ($ptaExists) {
                $errors[] = 'A Project Technical Assistant II account already exists. Only 1 PTA account is allowed in the system.';
            }
        } elseif ($role === 'cmi') {
            if (empty($institution)) {
                $errors[] = 'Institution is required.';
            } else {
                $cmiExists = User::where('role', 'cmi')
                    ->whereRaw('LOWER(TRIM(institution)) = ?', [mb_strtolower(trim($institution))])
                    ->whereIn('status', ['active', 'pending'])
                    ->exists();
                if ($cmiExists) {
                    $errors[] = 'This institution already has a CMI Representative account (active or pending approval). Only 1 CMI account is allowed per institution.';
                }
            }
        }
        if ($role !== 'pta' && empty($designation)) $errors[] = 'Designation / Position is required.';
        if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
        if ($password !== $pwConfirm) $errors[] = 'Passwords do not match.';

        if (!empty($errors)) {
            return response()->json(['success' => false, 'message' => implode(' ', $errors)]);
        }

        if (User::where('email', $email)->exists()) {
            return response()->json(['success' => false, 'message' => 'That email is already registered.']);
        }

        $otp       = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = now()->addMinutes(10);
        $hashed    = Hash::make($password);
        $instValue = ($role === 'cmi') ? $institution : null;
        $desgValue = !empty($designation) ? $designation : null;

        PendingRegistration::updateOrCreate(
            ['email' => $email],
            [
                'first_name'     => $firstName,
                'last_name'      => $lastName,
                'password'       => $hashed,
                'role'           => $role,
                'institution'    => $instValue,
                'designation'    => $desgValue,
                'otp'            => $otp,
                'otp_expires_at' => $expiresAt,
                'created_at'     => now(),
                'verified'       => false,
            ]
        );

        $fullName = $firstName . ' ' . $lastName;
        if (!$this->mailService->sendOtpEmail($email, $fullName, $otp)) {
            return response()->json(['success' => false, 'message' => 'Failed to send verification email. Please try again.']);
        }

        return response()->json([
            'success'  => true,
            'message'  => 'OTP sent! Check your email.',
            'redirect' => '../verify.php?email=' . urlencode($email),
        ]);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $action = $request->input('action', 'verify');
        $email  = trim($request->input('email', ''));
        $otp    = trim($request->input('otp', ''));

        if (empty($email)) {
            return response()->json(['success' => false, 'message' => 'Email is required.']);
        }

        if ($action === 'resend') {
            $pending = PendingRegistration::where('email', $email)->first();
            if (!$pending) {
                return response()->json(['success' => false, 'message' => 'No pending registration found.']);
            }

            $newOtp    = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expiresAt = now()->addMinutes(10);

            $pending->update([
                'otp'            => $newOtp,
                'otp_expires_at' => $expiresAt,
                'verified'       => false,
            ]);

            $fullName = $pending->first_name . ' ' . $pending->last_name;
            if (!$this->mailService->sendOtpEmail($email, $fullName, $newOtp)) {
                return response()->json(['success' => false, 'message' => 'Failed to resend OTP.']);
            }

            return response()->json(['success' => true, 'message' => 'A new OTP has been sent to your email.']);
        }

        if (empty($otp)) {
            return response()->json(['success' => false, 'message' => 'OTP is required.']);
        }

        $pending = PendingRegistration::where('email', $email)->first();
        if (!$pending) {
            return response()->json(['success' => false, 'message' => 'No pending registration found. Please register again.']);
        }

        if (now()->greaterThan($pending->otp_expires_at)) {
            return response()->json(['success' => false, 'message' => 'OTP has expired. Please request a new one.']);
        }

        if (!hash_equals($pending->otp, $otp)) {
            return response()->json(['success' => false, 'message' => 'Invalid OTP. Please try again.']);
        }

        $pending->update(['verified' => true]);

        return response()->json([
            'success' => true,
            'message' => 'OTP verified! Please review your details and confirm.',
        ]);
    }

    public function finalizeRegister(Request $request): JsonResponse
    {
        $email       = trim($request->input('email', ''));
        $firstName   = trim($request->input('first_name', ''));
        $lastName    = trim($request->input('last_name', ''));
        $password    = $request->input('password', '');
        $role        = $request->input('role', '');
        if ($role === 'guest') {
            $role = 'viewer';
        }
        $designation = trim($request->input('designation', ''));

        if (empty($email) || empty($firstName) || empty($lastName) || empty($password) || empty($role)) {
            return response()->json(['success' => false, 'message' => 'Missing required fields.']);
        }

        if ($role !== 'pta' && empty($designation)) {
            return response()->json(['success' => false, 'message' => 'Designation / Position is required.']);
        }

        $pending = PendingRegistration::where('email', $email)->where('verified', true)->first();
        if (!$pending) {
            return response()->json(['success' => false, 'message' => 'Email not verified. Please complete OTP verification first.']);
        }

        if (User::where('email', $email)->exists()) {
            $pending->delete();
            return response()->json(['success' => false, 'message' => 'That email is already registered. Please sign in.']);
        }

        if ($role === 'pta') {
            $ptaExists = User::where('role', 'pta')->exists();
            if ($ptaExists) {
                $pending->delete();
                return response()->json([
                    'success' => false,
                    'message' => 'A Project Technical Assistant II account already exists. Only 1 PTA account is allowed in the system.'
                ]);
            }
        }

        $hashed    = Hash::make($password);
        $instValue = !empty($pending->institution) ? $pending->institution : null;
        $desgValue = !empty($pending->designation) ? $pending->designation : null;
        $status    = ($role === 'pta') ? 'active' : 'pending';

        if ($role === 'cmi') {
            $cmiExists = User::where('role', 'cmi')
                ->whereRaw('LOWER(TRIM(institution)) = ?', [mb_strtolower(trim((string)$instValue))])
                ->whereIn('status', ['active', 'pending'])
                ->exists();
            if ($cmiExists) {
                $pending->delete();
                return response()->json([
                    'success' => false,
                    'message' => 'This institution already has a CMI Representative account (active or pending approval). Only 1 CMI account is allowed per institution.'
                ]);
            }
        }

        try {
            DB::transaction(function () use ($firstName, $lastName, $email, $hashed, $role, $instValue, $desgValue, $status, $pending) {
                User::create([
                    'first_name'  => $firstName,
                    'last_name'   => $lastName,
                    'email'       => $email,
                    'password'    => $hashed,
                    'role'        => $role,
                    'institution' => $instValue,
                    'designation' => $desgValue,
                    'status'      => $status,
                ]);

                $pending->delete();
            });

            if ($role === 'pta') {
                $message = 'Account created successfully! You may now sign in.';
                $redirect = '/login';
            } else {
                $message = 'Account created! Please wait for PTA approval before signing in.';
                $redirect = '/login?registered=1';
            }

            return response()->json(['success' => true, 'message' => $message, 'redirect' => $redirect]);
        } catch (Throwable $e) {
            Log::error('finalizeRegister error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Registration failed. Please try again.']);
        }
    }

    /**
     * Verify OTP for Forgot Password flow (session-based, not PendingRegistration).
     */
    public function verifyOtpFp(Request $request): JsonResponse
    {
        $email = trim($request->input('email', ''));
        $otp   = trim($request->input('otp', ''));

        if (!$email || !$otp) {
            return response()->json(['success' => false, 'message' => 'Email and OTP are required.']);
        }

        if (empty(session('fp_otp')) || empty(session('fp_email')) || session('fp_email') !== $email) {
            return response()->json(['success' => false, 'message' => 'Session expired. Please request a new OTP.']);
        }

        if (strtotime(session('fp_expiry')) < time()) {
            session()->forget(['fp_otp', 'fp_email', 'fp_expiry']);
            return response()->json(['success' => false, 'message' => 'OTP has expired. Please request a new one.']);
        }

        $otpStripped = ltrim($otp, '0') ?: '0';
        if (!Hash::check($otp, session('fp_otp')) && !Hash::check($otpStripped, session('fp_otp'))) {
            return response()->json(['success' => false, 'message' => 'Invalid OTP. Please try again.']);
        }

        // Mark as verified so resetPassword can proceed
        session(['fp_verified' => true]);

        return response()->json(['success' => true, 'message' => 'OTP verified successfully.']);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $email = trim($request->input('email', ''));
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['success' => false, 'message' => 'Invalid email address.']);
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'No account found with that email.']);
        }

        $otp    = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiry = now()->addMinutes(10)->toDateTimeString();

        session([
            'fp_otp'    => Hash::make($otp),
            'fp_email'  => $email,
            'fp_expiry' => $expiry,
        ]);

        $sent = $this->mailService->sendOtpEmail($email, $user->first_name, $otp);

        if ($sent) {
            return response()->json(['success' => true, 'message' => 'OTP sent! Check your Gmail inbox.']);
        }

        return response()->json(['success' => false, 'message' => 'Failed to send email. Please try again.']);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $email    = trim($request->input('email', ''));
        $otp      = trim($request->input('otp', ''));
        $password = trim($request->input('password', ''));

        if (!$email || !$otp || !$password) {
            return response()->json(['success' => false, 'message' => 'All fields are required.']);
        }
        if (strlen($password) < 8) {
            return response()->json(['success' => false, 'message' => 'Password must be at least 8 characters.']);
        }

        if (empty(session('fp_otp')) || empty(session('fp_email')) || session('fp_email') !== $email) {
            return response()->json(['success' => false, 'message' => 'Session expired. Please request a new OTP.']);
        }

        if (strtotime(session('fp_expiry')) < time()) {
            session()->forget(['fp_otp', 'fp_email', 'fp_expiry']);
            return response()->json(['success' => false, 'message' => 'OTP has expired. Please request a new one.']);
        }

        $otpStripped = ltrim($otp, '0') ?: '0';
        if (!Hash::check($otp, session('fp_otp')) && !Hash::check($otpStripped, session('fp_otp'))) {
            return response()->json(['success' => false, 'message' => 'Invalid OTP. Please try again.']);
        }

        if (empty(session('fp_verified'))) {
            return response()->json(['success' => false, 'message' => 'OTP not verified. Please complete OTP verification first.']);
        }

        $user = User::where('email', $email)->first();
        if ($user) {
            $user->update(['password' => Hash::make($password)]);
            session()->forget(['fp_otp', 'fp_email', 'fp_expiry', 'fp_verified']);
            return response()->json(['success' => true, 'message' => 'Password reset successfully.']);
        }

        return response()->json(['success' => false, 'message' => 'Could not update password. Please try again.']);
    }

    public function getOccupiedInstitutions(): JsonResponse
    {
        $occupied = User::where('role', 'cmi')
            ->whereIn('status', ['active', 'pending'])
            ->whereNotNull('institution')
            ->where('institution', '!=', '')
            ->pluck('institution')
            ->unique()
            ->values();

        $ptaExists = User::where('role', 'pta')->exists();

        return response()->json([
            'success'    => true,
            'occupied'   => $occupied,
            'pta_exists' => $ptaExists,
        ]);
    }
}
