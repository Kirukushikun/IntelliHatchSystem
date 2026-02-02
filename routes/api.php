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

Route::middleware('auth:sanctum')->group(function () {
    // Form Statistics
    Route::get('/form-stats', [FormStatsController::class, 'stats']);
    Route::get('/form-stats/quick', [FormStatsController::class, 'quickStats']);
});
