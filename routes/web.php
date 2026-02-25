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
use App\Http\Controllers\Admin\FormsPrintController;
use App\Http\Controllers\Admin\InsightsController;

Route::get('/', function () {
    return view('landing');
});

// Public form routes
Route::get('/forms/incubator-routine', function () {
    return view('shared.forms.incubator-routine');
})->name('forms.incubator-routine');

Route::get('/forms/blower-air-hatcher', function () {
    return view('shared.forms.blower-air-hatcher');
})->name('forms.blower-air-hatcher');

Route::get('/forms/blower-air-incubator', function () {
    return view('shared.forms.blower-air-incubator');
})->name('forms.blower-air-incubator');

Route::get('/forms/hatchery-sullair', function () {
    return view('shared.forms.hatchery-sullair');
})->name('forms.hatchery-sullair');

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
        
        Route::get('/admin/forms/blower-air-hatcher-routine', [FormController::class, 'blowerAirHatcher'])->name('admin.forms.blower-air-hatcher-routine');

        Route::get('/admin/forms/blower-air-incubator-routine', [FormController::class, 'blowerAirIncubator'])->name('admin.forms.blower-air-incubator-routine');

        Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

        Route::get('/admin/insights', [InsightsController::class, 'index'])->name('admin.insights');

        Route::get('/admin/incubator-routine-dashboard', [DashboardController::class, 'incubatorRoutine'])->name('admin.incubator-routine-dashboard');

        Route::get('/admin/blower-air-hatcher-dashboard', [DashboardController::class, 'blowerAirHatcher'])->name('admin.blower-air-hatcher-dashboard');

        Route::get('/admin/blower-air-incubator-dashboard', [DashboardController::class, 'blowerAirIncubator'])->name('admin.blower-air-incubator-dashboard');

        Route::get('/admin/hatchery-sullair-dashboard', [DashboardController::class, 'hatcherySullair'])->name('admin.hatchery-sullair-dashboard');

        Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users');

        Route::get('/admin/incubator-machines', [IncubatorController::class, 'index'])->name('admin.incubator-machines');

        Route::get('/admin/hatcher-machines', [HatcherController::class, 'index'])->name('admin.hatcher-machines');

        Route::get('/admin/plenum-machines', [PlenumController::class, 'index'])->name('admin.plenum-machines');

        Route::get('/admin/print/forms/blower-air-hatcher', [FormsPrintController::class, 'blowerAirHatcher'])
            ->middleware('signed')
            ->name('admin.print.forms.blower-air-hatcher');

        Route::get('/admin/print/forms/blower-air-incubator', [FormsPrintController::class, 'blowerAirIncubator'])
            ->middleware('signed')
            ->name('admin.print.forms.blower-air-incubator');

        Route::get('/admin/print/forms/hatchery-sullair', [FormsPrintController::class, 'hatcherySullair'])
            ->middleware('signed')
            ->name('admin.print.forms.hatchery-sullair');

        Route::get('/admin/print/forms/incubator-routine', [FormsPrintController::class, 'incubatorRoutine'])
            ->middleware('signed')
            ->name('admin.print.forms.incubator-routine');

        Route::get('/admin/print/performance/incubator-routine', [FormsPrintController::class, 'incubatorRoutinePerformance'])
            ->middleware('signed')
            ->name('admin.print.performance.incubator-routine');

        Route::get('/admin/change-password', [UserController::class, 'changePassword'])->name('admin.change-password');
    });
    
    // User routes
    Route::middleware('user')->group(function () {
        Route::get('/user/forms', function () {
            return view('shared.forms');
        })->name('user.forms');

        Route::get('/user/forms/incubator-routine', [FormController::class, 'incubatorRoutine'])->name('user.forms.incubator-routine');
        
        Route::get('/user/forms/blower-air-hatcher', [FormController::class, 'blowerAirHatcher'])->name('user.forms.blower-air-hatcher');
        
        Route::get('/user/forms/blower-air-incubator', [FormController::class, 'blowerAirIncubator'])->name('user.forms.blower-air-incubator');
        
        Route::get('/user/forms/hatchery-sullair', [FormController::class, 'hatcherySullair'])->name('user.forms.hatchery-sullair');
        
        Route::get('/user/incubator-machines', [IncubatorController::class, 'index'])->name('user.incubator-machines');

        Route::get('/user/hatcher-machines', [HatcherController::class, 'index'])->name('user.hatcher-machines');

        Route::get('/user/plenum-machines', [PlenumController::class, 'index'])->name('user.plenum-machines');

        Route::get('/user/change-password', [UserController::class, 'changePassword'])->name('user.change-password');
    });
    
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});