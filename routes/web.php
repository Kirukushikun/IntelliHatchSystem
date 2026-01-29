<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect(((int) Auth::user()->user_type) === 0 ? '/admin/form-collection' : '/user/form-collection');
    }

    return redirect('/login');
});

// Guest routes (no authentication required)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::get('/user/forms', function () {
        return view('shared.forms');
    })->name('user.forms');

    Route::get('/admin/forms', function () {
        return view('shared.forms');
    })->name('admin.forms');

    // Form routes with new structure
    Route::get('/user/forms/incubator-routine', function () {
        return view('shared.forms.incubator-routine');
    })->name('user.forms.incubator-routine');

    Route::get('/admin/forms/incubator-routine', function () {
        return view('shared.forms.incubator-routine');
    })->name('admin.forms.incubator-routine');
    
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});