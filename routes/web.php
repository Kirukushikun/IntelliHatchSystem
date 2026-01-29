<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect(((int) Auth::user()->user_type) === 0 ? '/admin/dashboard' : '/user/forms');
    }

    return redirect('/login');
});

// Guest routes (no authentication required)
Route::middleware('guest')->group(function () {
    // User login routes
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
    
    // Admin login routes
    Route::get('/admin/login', [LoginController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/admin/login', [LoginController::class, 'login'])->name('admin.login.submit');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    // User routes
    Route::middleware('user')->group(function () {
        Route::get('/user/forms', function () {
            return view('shared.forms');
        })->name('user.forms');

        Route::get('/user/forms/incubator-routine', function () {
            return view('shared.forms.incubator-routine');
        })->name('user.forms.incubator-routine');
    });

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
    });

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});