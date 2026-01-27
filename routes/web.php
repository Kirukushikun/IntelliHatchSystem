<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IncubatorRoutineController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/incubator-routine', function () {
    return view('incubator-routine');
});

Route::post('/incubator-routine', [IncubatorRoutineController::class, 'store'])->name('incubator-routine.store');