<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Shared\Management\HatcherController;
use App\Http\Controllers\Shared\Management\IncubatorController;
use App\Http\Controllers\Shared\Management\PlenumController;
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
    // Unified login route
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
    
    // Legacy admin login routes (redirect to unified login)
    Route::get('/admin/login', function () {
        return redirect('/login');
    })->name('admin.login');
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

        Route::get('/admin/plenum-machines', [PlenumController::class, 'index'])->name('admin.plenum-machines');

        Route::get('/admin/change-password', [UserController::class, 'changePassword'])->name('admin.change-password');
    });
    
    // User routes
    Route::middleware('user')->group(function () {
        Route::get('/user/forms', function () {
            return view('users.forms');
        })->name('user.forms');

        Route::get('/user/forms/incubator-routine', function () {
            return view('shared.forms.incubator-routine');
        })->name('user.forms.incubator-routine');
        
        Route::get('/user/incubator-machines', [IncubatorController::class, 'index'])->name('user.incubator-machines');

        Route::get('/user/hatcher-machines', [HatcherController::class, 'index'])->name('user.hatcher-machines');

        Route::get('/user/plenum-machines', [PlenumController::class, 'index'])->name('user.plenum-machines');

        Route::get('/user/change-password', [UserController::class, 'changePassword'])->name('user.change-password');
    });
    
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});