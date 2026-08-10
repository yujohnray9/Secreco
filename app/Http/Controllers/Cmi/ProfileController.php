<?php

namespace App\Http\Controllers\Cmi;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function get(): JsonResponse
    {
        $userId = Auth::id() ?? session('user_id');
        $user   = User::find($userId);

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        $roleLabel = match ($user->role) {
            'pta'    => 'Project Technical Assistant',
            'cmi'    => 'CMI Representative',
            'viewer' => 'Viewer',
            default  => ucfirst($user->role),
        };

        $photo = $user->profile_picture;
        if ($photo && !str_starts_with($photo, 'storage/') && !str_starts_with($photo, '/storage/') && !str_starts_with($photo, 'assets/')) {
            $photo = 'storage/' . $photo;
        }

        return response()->json([
            'success'      => true,
            'first_name'   => $user->first_name,
            'last_name'    => $user->last_name,
            'name'         => $user->name,
            'email'        => $user->email,
            'role'         => $user->role,
            'role_label'   => $roleLabel,
            'institution'  => $user->institution ?? '',
            'designation'  => $user->designation ?? '',
            'status'       => $user->status,
            'member_since' => $user->created_at ? $user->created_at->format('F j, Y') : '',
            'photo'        => $photo,
        ]);
    }

    public function save(Request $request): JsonResponse
    {
        $userId = Auth::id() ?? session('user_id');
        $user   = User::find($userId);

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        $firstName   = trim($request->input('first_name', ''));
        $lastName    = trim($request->input('last_name', ''));
        $designation = trim($request->input('designation', ''));

        if ($firstName === '' || $lastName === '') {
            return response()->json(['success' => false, 'message' => 'First name and last name cannot be empty.']);
        }
        if ($designation === '') {
            return response()->json(['success' => false, 'message' => 'Designation / Position cannot be empty.']);
        }
        if (mb_strlen($firstName) > 100 || mb_strlen($lastName) > 100) {
            return response()->json(['success' => false, 'message' => 'First and last name must be 100 characters or fewer.']);
        }
        if (mb_strlen($designation) > 255) {
            return response()->json(['success' => false, 'message' => 'Designation / Position must be 255 characters or fewer.']);
        }

        $firstName = mb_strtoupper($firstName);
        $lastName  = mb_strtoupper($lastName);

        $user->update([
            'first_name'  => $firstName,
            'last_name'   => $lastName,
            'designation' => $designation,
        ]);

        session([
            'user_name'  => $firstName . ' ' . $lastName,
            'user_desig' => $designation,
        ]);

        ActivityLogService::log($userId, "Updated own profile: {$firstName} {$lastName} ({$designation})");

        return response()->json([
            'success'     => true,
            'first_name'  => $firstName,
            'last_name'   => $lastName,
            'name'        => $firstName . ' ' . $lastName,
            'designation' => $designation,
        ]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $userId = Auth::id() ?? session('user_id');
        $user   = User::find($userId);

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        $currentPw = $request->input('current_password', '');
        $newPw     = $request->input('new_password', '');

        if ($currentPw === '' || $newPw === '') {
            return response()->json(['success' => false, 'message' => 'All fields are required.']);
        }
        if (strlen($newPw) < 8) {
            return response()->json(['success' => false, 'message' => 'New password must be at least 8 characters.']);
        }

        if (!Hash::check($currentPw, $user->password)) {
            return response()->json(['success' => false, 'message' => 'Current password is incorrect.']);
        }

        if (Hash::check($newPw, $user->password)) {
            return response()->json(['success' => false, 'message' => 'New password must be different from your current password.']);
        }

        $user->update(['password' => Hash::make($newPw)]);

        return response()->json(['success' => true]);
    }

    public function uploadPhoto(Request $request): JsonResponse
    {
        if (!$request->hasFile('photo') || !$request->file('photo')->isValid()) {
            return response()->json(['success' => false, 'message' => 'No file uploaded.']);
        }

        $userId = Auth::id() ?? session('user_id');
        $user   = User::find($userId);

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.']);
        }

        $file     = $request->file('photo');
        $mimeType = $file->getMimeType();
        $allowed  = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        if (!in_array($mimeType, $allowed, true)) {
            return response()->json(['success' => false, 'message' => 'Invalid file type.']);
        }

        if ($file->getSize() > 5 * 1024 * 1024) {
            return response()->json(['success' => false, 'message' => 'File too large (max 5MB).']);
        }

        $filename  = 'profile_' . $userId . '_' . time() . '.jpg';
        $path      = $file->storeAs('uploads/profiles', $filename, 'public');
        $photoPath = 'storage/uploads/profiles/' . $filename;

        $user->update(['profile_picture' => $photoPath]);
        session(['user_photo' => $photoPath]);

        return response()->json(['success' => true, 'photo' => $photoPath]);
    }

    public function removePhoto(Request $request): JsonResponse
    {
        $userId = Auth::id() ?? session('user_id');
        $user   = User::find($userId);

        if ($user) {
            $user->update(['profile_picture' => null]);
            session(['user_photo' => null]);
        }

        return response()->json(['success' => true]);
    }
}
