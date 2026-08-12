<?php

use App\Http\Controllers\Pages\CmiPageController;
use App\Http\Controllers\Pages\PtaPageController;
use App\Http\Controllers\Pages\ViewerPageController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/dashboard/' . Auth::user()->role);
    }
    return view('welcome');
});

Route::get('/login', function () {
    if (Auth::check()) return redirect('/dashboard/' . Auth::user()->role);
    return view('auth.login');
})->name('login');

Route::get('/verify-captcha', function () {
    if (Auth::check()) return redirect('/dashboard/' . Auth::user()->role);
    return view('auth.verify-captcha');
})->name('verify-captcha');

Route::prefix('dashboard')->group(function () {
    Route::get('/cmi/{page?}', [CmiPageController::class, 'show'])->middleware(['auth.custom', 'role:cmi,pta']);
    Route::get('/pta/{page?}', [PtaPageController::class, 'show'])->middleware(['auth.custom', 'role:pta']);
    Route::get('/viewer/{page?}', [ViewerPageController::class, 'show'])->middleware(['auth.custom', 'role:viewer']);
});
