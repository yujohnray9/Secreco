<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PtaPageController extends Controller
{
    public function show(Request $request, string $page = 'dashboard')
    {
        $allowed = ['dashboard', 'submissions', 'reports', 'users', 'institutions', 'formats', 'notifications', 'settings'];
        if (!in_array($page, $allowed, true)) {
            $page = 'dashboard';
        }

        return view("pta.{$page}", [
            'currentPage' => $page,
            'userName'    => session('user_name', 'PTA User'),
            'userRole'    => session('user_role', 'pta'),
            'userInst'    => session('user_inst', ''),
            'userDesig'   => session('user_desig', ''),
            'userPhoto'   => session('user_photo', null),
        ]);
    }
}
