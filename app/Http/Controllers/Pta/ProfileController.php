<?php

namespace App\Http\Controllers\Pta;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
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

        $ext      = 'jpg';
        $filename = 'profile_' . $userId . '_' . time() . '.' . $ext;
        $path     = $file->storeAs('uploads/profiles', $filename, 'public');
        $photoPath = 'uploads/profiles/' . $filename;

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
