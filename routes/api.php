<?php

use App\Http\Controllers\Api\FormStatsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function () {
    // Form Statistics
    Route::get('/form-stats', [FormStatsController::class, 'index'])->name('api.form-stats.index');
    Route::get('/form-stats/quick', [FormStatsController::class, 'quickStats'])->name('api.form-stats.quick');
    Route::get('/form-types', [FormStatsController::class, 'formTypes'])->name('api.form-types.index');
});
