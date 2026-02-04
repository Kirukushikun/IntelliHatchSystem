<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FormStatsController;
use App\Http\Middleware\ApiKeyMiddleware;

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

Route::middleware([ApiKeyMiddleware::class, 'throttle:60,1'])->group(function () {
    // Get form statistics with dynamic filtering
    Route::get('/form-stats', [FormStatsController::class, 'index']);
    
    // Get available form types
    Route::get('/form-types', [FormStatsController::class, 'formTypes']);
});

