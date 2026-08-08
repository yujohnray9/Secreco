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

        return view("cmi.{$page}", [
            'currentPage' => $page,
            'userName'    => session('user_name', 'CMI User'),
            'userRole'    => session('user_role', 'cmi'),
            'userInst'    => session('user_inst', ''),
            'userDesig'   => session('user_desig', ''),
            'userPhoto'   => session('user_photo', null),
        ]);
    }
}
