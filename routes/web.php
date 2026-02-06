<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\HatcherController;
use App\Http\Controllers\Admin\IncubatorController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\FormController;

Route::get('/', function () {
    return view('landing');
});

// Public form routes
Route::get('/forms/incubator-routine', function () {
    return view('shared.forms.incubator-routine');
})->name('forms.incubator-routine');

// Guest routes (no authentication required)
Route::middleware('guest')->group(function () {
    // Admin login routes
    Route::get('/admin/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/admin/login', [LoginController::class, 'login'])->name('login.submit');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Admin routes
    Route::middleware('admin')->group(function () {
        Route::get('/admin/forms', [FormController::class, 'index'])->name('admin.forms');

        Route::get('/admin/forms/incubator-routine', [FormController::class, 'incubatorRoutine'])->name('admin.forms.incubator-routine');
        
        Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

        Route::get('/admin/incubator-routine-dashboard', [DashboardController::class, 'incubatorRoutine'])->name('admin.incubator-routine-dashboard');

        Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users');

        Route::get('/admin/incubator-machines', [IncubatorController::class, 'index'])->name('admin.incubator-machines');

        Route::get('/admin/hatcher-machines', [HatcherController::class, 'index'])->name('admin.hatcher-machines');

        Route::get('/admin/change-password', [UserController::class, 'changePassword'])->name('admin.change-password');
    });

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});