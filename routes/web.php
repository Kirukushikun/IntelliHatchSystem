<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;

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
        Route::get('/admin/forms', function () {
            return view('shared.forms');
        })->name('admin.forms');

        Route::get('/admin/forms/incubator-routine', function () {
            return view('shared.forms.incubator-routine');
        })->name('admin.forms.incubator-routine');
        
        Route::get('/admin/dashboard', function () {
            return view('admin.dashboard');
        })->name('admin.dashboard');

        Route::get('/admin/incubator-routine-dashboard', function () {
            return view('admin.incubator-routine-dashboard');
        })->name('admin.incubator-routine-dashboard');

        Route::get('/admin/users', function () {
            return view('admin.users');
        })->name('admin.users');

        Route::get('/admin/incubators', function () {
            return view('admin.incubators');
        })->name('admin.incubators');

        Route::get('/admin/change-password', function () {
            return view('auth.change-password-page');
        })->name('admin.change-password');
    });

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});