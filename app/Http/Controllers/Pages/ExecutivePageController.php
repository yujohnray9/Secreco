<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ExecutivePageController extends Controller
{
    public function show(Request $request, string $page = 'dashboard')
    {
        if ($page === 'institution') {
            $page = 'institutions';
        }
        $allowed = ['dashboard', 'institutions', 'profile'];
        if (!in_array($page, $allowed, true)) {
            $page = 'dashboard';
        }

        return view("executive.{$page}", [
            'currentPage' => $page,
            'userName'    => session('user_name', 'Executive Director'),
            'userRole'    => session('user_role', 'executive'),
            'userInst'    => session('user_inst', ''),
            'userDesig'   => session('user_desig', 'Executive Director'),
            'userPhoto'   => session('user_photo', null),
        ]);
    }
}
