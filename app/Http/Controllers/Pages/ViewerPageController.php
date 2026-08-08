<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ViewerPageController extends Controller
{
    public function show(Request $request, string $page = 'dashboard')
    {
        $allowed = ['dashboard', 'submissions', 'reports', 'institutions', 'formats', 'settings', 'notifications', 'users'];
        if (!in_array($page, $allowed, true)) {
            $page = 'dashboard';
        }

        return view("viewer.{$page}", [
            'currentPage' => $page,
            'userName'    => session('user_name', 'Viewer User'),
            'userRole'    => session('user_role', 'viewer'),
            'userInst'    => session('user_inst', ''),
            'userDesig'   => session('user_desig', ''),
            'userPhoto'   => session('user_photo', null),
        ]);
    }
}
