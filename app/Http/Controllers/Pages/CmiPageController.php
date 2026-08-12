<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CmiPageController extends Controller
{
    public function show(Request $request, string $page = 'dashboard')
    {
        $allowed = ['dashboard', 'fillup', 'drafts', 'submissions', 'profile', 'notifications'];
        if (!in_array($page, $allowed, true)) {
            $page = 'dashboard';
        }

        $targetUserId = (int) $request->input('cmi_user_id', 0);
        $targetUser   = $targetUserId > 0 ? \App\Models\User::find($targetUserId) : null;

        $userInst = $targetUser?->institution ?: session('user_inst', '');
        $userName = $targetUser ? ($targetUser->first_name . ' ' . $targetUser->last_name) : session('user_name', 'CMI User');

        return view("cmi.{$page}", [
            'currentPage'  => $page,
            'userName'     => $userName,
            'userRole'     => session('user_role', 'cmi'),
            'userInst'     => $userInst,
            'userDesig'    => $targetUser?->designation ?: session('user_desig', ''),
            'userPhoto'    => $targetUser?->profile_picture ?: session('user_photo', null),
            'targetUserId' => $targetUserId,
        ]);
    }
}
