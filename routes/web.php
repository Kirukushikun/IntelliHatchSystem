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
    Route::get('/user/form-collection', function () {
        return view('shared.form-collection');
    })->name('user.form-collection');

    Route::get('/admin/form-collection', function () {
        return view('shared.form-collection');
    })->name('admin.form-collection');

    Route::get('/incubator-routine', function () {
        return view('forms.incubator-routine');
    })->name('incubator-routine');
    
    
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});