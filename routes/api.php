<?php

use App\Http\Controllers\Api\V1\AnalysisController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DeviceController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\ScenarioController;
use Illuminate\Support\Facades\Route;

Route::pattern('device', '[1-9][0-9]{0,17}');
Route::pattern('analysis', '[1-9][0-9]{0,17}');
Route::pattern('scenario', '[1-9][0-9]{0,17}');

Route::prefix('v1')->group(function (): void {
    Route::get('health', HealthController::class)->middleware('throttle:health');
    Route::post('auth/register', [AuthController::class, 'register'])->middleware('throttle:register');
    Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function (): void {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::apiResource('devices', DeviceController::class)->except('update');
        Route::patch('devices/{device}', [DeviceController::class, 'update']);
        Route::post('devices/{device}/analyses', [AnalysisController::class, 'store']);
        Route::get('analyses', [AnalysisController::class, 'index']);
        Route::get('analyses/{analysis}', [AnalysisController::class, 'show']);
        Route::post('analyses/{analysis}/scenarios', [ScenarioController::class, 'store']);
        Route::get('analyses/{analysis}/scenarios', [ScenarioController::class, 'index']);
        Route::get('scenarios/{scenario}', [ScenarioController::class, 'show']);
    });
});
