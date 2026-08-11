<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\UserApprovalController;
use App\Http\Controllers\Cmi\DashboardController as CmiDashboardController;
use App\Http\Controllers\Cmi\ProfileController as CmiProfileController;
use App\Http\Controllers\Cmi\ReportController as CmiReportController;
use App\Http\Controllers\Cmi\TableController as CmiTableController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Pta\DashboardController as PtaDashboardController;
use App\Http\Controllers\Pta\FormatController as PtaFormatController;
use App\Http\Controllers\Pta\InstitutionController as PtaInstitutionController;
use App\Http\Controllers\Pta\NotificationController as PtaNotificationController;
use App\Http\Controllers\Pta\ProfileController as PtaProfileController;
use App\Http\Controllers\Pta\ReportController as PtaReportController;
use App\Http\Controllers\Pta\SettingsController as PtaSettingsController;
use App\Http\Controllers\Pta\SubmissionController as PtaSubmissionController;
use App\Http\Controllers\Pta\UserController as PtaUserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/finalize-register', [AuthController::class, 'finalizeRegister']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/verify-otp-fp', [AuthController::class, 'verifyOtpFp']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

/*
|--------------------------------------------------------------------------
| CMI Routes (Requires Auth & CMI Role)
|--------------------------------------------------------------------------
*/
Route::prefix('cmi')->middleware(['auth.custom', 'role:cmi'])->group(function () {
    Route::get('/dashboard', [CmiDashboardController::class, 'getData']);
    Route::get('/tables/statuses', [CmiTableController::class, 'statuses']);
    Route::get('/tables/load', [CmiTableController::class, 'load']);
    Route::post('/tables/save', [CmiTableController::class, 'save']);
    Route::post('/tables/upload-doc', [CmiTableController::class, 'uploadDoc']);
    Route::post('/tables/delete-doc', [CmiTableController::class, 'deleteDoc']);

    Route::post('/report/submit', [CmiReportController::class, 'submit']);

    Route::get('/profile', [CmiProfileController::class, 'get']);
    Route::post('/profile/save', [CmiProfileController::class, 'save']);
    Route::post('/profile/change-password', [CmiProfileController::class, 'changePassword']);
    Route::post('/profile/upload-photo', [CmiProfileController::class, 'uploadPhoto']);
    Route::post('/profile/remove-photo', [CmiProfileController::class, 'removePhoto']);
});

/*
|--------------------------------------------------------------------------
| PTA Routes (Requires Auth & PTA Role)
|--------------------------------------------------------------------------
*/
Route::prefix('pta')->middleware(['auth.custom', 'role:pta'])->group(function () {
    Route::get('/dashboard/stats', [PtaDashboardController::class, 'getStats']);

    Route::get('/submissions', [PtaSubmissionController::class, 'index']);
    Route::post('/submissions/accept', [PtaSubmissionController::class, 'accept']);
    Route::post('/submissions/request-correction', [PtaSubmissionController::class, 'requestCorrection']);
    Route::post('/submissions/delete', [PtaSubmissionController::class, 'delete']);
    Route::post('/submissions/update-table', [PtaSubmissionController::class, 'updateTable']);

    Route::get('/users', [PtaUserController::class, 'index']);
    Route::get('/users/pending', [PtaUserController::class, 'getPending']);
    Route::post('/users/create', [PtaUserController::class, 'create']);
    Route::post('/users/approve', [PtaUserController::class, 'approve']);
    Route::post('/users/approve-user', [UserApprovalController::class, 'approveUser']);
    Route::post('/users/toggle-user-status', [UserApprovalController::class, 'toggleUserStatus']);

    Route::get('/institutions', [PtaInstitutionController::class, 'index']);

    Route::get('/formats', [PtaFormatController::class, 'index']);
    Route::post('/formats/save', [PtaFormatController::class, 'save']);

    Route::get('/reports/consolidated', [PtaReportController::class, 'getConsolidated']);

    Route::post('/settings/save', [PtaSettingsController::class, 'saveSettings']);
    Route::get('/settings/audit', [PtaSettingsController::class, 'getAudit']);

    Route::post('/profile/upload-photo', [PtaProfileController::class, 'uploadPhoto']);
    Route::post('/profile/remove-photo', [PtaProfileController::class, 'removePhoto']);

    Route::get('/notifications', [PtaNotificationController::class, 'get']);
});

/*
|--------------------------------------------------------------------------
| Shared Routes (Requires Auth)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth.custom'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'get']);
    Route::post('/notifications/mark-read', [NotificationController::class, 'markRead']);
});
