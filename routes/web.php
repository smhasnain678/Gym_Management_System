<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FeeController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MembershipPlanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OfflineSyncController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TrainerController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| WarmUp — Web Routes
|--------------------------------------------------------------------------
|
| Version 1 — Single-role (Gym Owner) authentication only.
| Members, Trainers, and Staff are managed as records, not accounts.
|
*/

// -------------------------------------------------------------------------
// Root redirect: send guests to login, authenticated users to dashboard
// -------------------------------------------------------------------------
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

// -------------------------------------------------------------------------
// Guest-only routes (redirect to dashboard if already authenticated)
// -------------------------------------------------------------------------
Route::middleware('guest')->group(function () {

    // Login
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    // Forgot Password
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])
         ->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])
         ->name('password.email');

    // Reset Password
    Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])
         ->name('password.reset');
    Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])
         ->name('password.update');
});

// -------------------------------------------------------------------------
// Authenticated routes (redirect to login if not authenticated)
// -------------------------------------------------------------------------
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    
    // Activity Logs
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::post('/notifications/{notification}/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::get('/api/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('api.notifications.unread-count');


    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::patch('/settings', [SettingsController::class, 'update'])->name('settings.update');

    // Backup & Restore (Phase 14)
    Route::get('/settings/backup/download', [SettingsController::class, 'downloadBackup'])->name('settings.backup.download');
    Route::post('/settings/backup/restore', [SettingsController::class, 'restoreBackup'])->name('settings.backup.restore');

    // Membership Plans
    Route::resource('membership-plans', MembershipPlanController::class);
    Route::patch('membership-plans/{membershipPlan}/toggle-status', [MembershipPlanController::class, 'toggleStatus'])
         ->name('membership-plans.toggle-status');

    // Members
    Route::resource('members', MemberController::class);
    Route::post('members/{member}/assign-membership',  [MemberController::class, 'assignMembership'])->name('members.assign-membership');
    Route::post('members/{member}/renew-membership',   [MemberController::class, 'renewMembership'])->name('members.renew-membership');
    Route::post('members/{member}/record-payment',     [MemberController::class, 'recordPayment'])->name('members.record-payment');
    Route::patch('members/{member}/toggle-status',     [MemberController::class, 'toggleStatus'])->name('members.toggle-status');

    // Trainers
    Route::resource('trainers', TrainerController::class);
    Route::patch('trainers/{trainer}/toggle-status', [TrainerController::class, 'toggleStatus'])->name('trainers.toggle-status');

    // Attendance
    Route::resource('attendances', App\Http\Controllers\AttendanceController::class)->only(['index', 'store', 'update']);
    Route::patch('attendances/{attendance}/checkout', [App\Http\Controllers\AttendanceController::class, 'checkout'])->name('attendances.checkout');

    // Fee Management
    Route::get('/fees',                      [FeeController::class, 'index'])->name('fees.index');
    Route::get('/fees/history',              [FeeController::class, 'history'])->name('fees.history');
    Route::post('/fees/pay',                 [FeeController::class, 'pay'])->name('fees.pay');
    Route::get('/fees/receipt/{payment}',    [FeeController::class, 'receipt'])->name('fees.receipt');

    // Reports
    Route::get('/reports', [App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/revenue', [App\Http\Controllers\ReportController::class, 'revenue'])->name('reports.revenue');
    Route::get('/reports/attendance', [App\Http\Controllers\ReportController::class, 'attendance'])->name('reports.attendance');
    Route::get('/reports/members', [App\Http\Controllers\ReportController::class, 'members'])->name('reports.members');
    Route::get('/reports/members/{member}/print', [App\Http\Controllers\ReportController::class, 'printMember'])->name('reports.members.print');
    Route::get('/reports/memberships', [App\Http\Controllers\ReportController::class, 'memberships'])->name('reports.memberships');
    Route::get('/reports/trainers', [App\Http\Controllers\ReportController::class, 'trainers'])->name('reports.trainers');
    Route::get('/reports/fees', [App\Http\Controllers\ReportController::class, 'fees'])->name('reports.fees');
    Route::get('/reports/expenses', [App\Http\Controllers\ReportController::class, 'expenses'])->name('reports.expenses');

    // Expense Management
    Route::resource('expenses', ExpenseController::class)->except(['show']);
    // Global Search
    Route::get('/api/search', [App\Http\Controllers\SearchController::class, 'index'])->name('api.search');

    // Offline Sync API (Phase 14)
    Route::post('/api/offline/sync', [OfflineSyncController::class, 'sync'])->name('api.offline.sync');
});
