<?php

use App\Http\Controllers\Web\AnalysisController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DeviceController;
use App\Http\Controllers\Web\ScenarioController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::pattern('device', '[1-9][0-9]{0,17}');
Route::pattern('analysis', '[1-9][0-9]{0,17}');

Route::get('/', fn () => redirect()->route(Auth::guard('web')->check() ? 'dashboard' : 'login'))->name('home');
Route::middleware('guest:web')->group(function (): void {
    Route::get('login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:login')->name('login.store');
    Route::get('register', [AuthController::class, 'registerForm'])->name('register');
    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:register')->name('register.store');
});

Route::middleware(['auth:web', 'throttle:api'])->group(function (): void {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::resource('devices', DeviceController::class)->except('update')->names('web.devices');
    Route::patch('devices/{device}', [DeviceController::class, 'update'])->name('web.devices.update');
    Route::get('analyses', [AnalysisController::class, 'index'])->name('web.analyses.index');
    Route::get('analyses/create', [AnalysisController::class, 'create'])->name('web.analyses.create');
    Route::post('analyses', [AnalysisController::class, 'store'])->name('web.analyses.store');
    Route::get('analyses/{analysis}', [AnalysisController::class, 'show'])->name('web.analyses.show');
    Route::post('analyses/{analysis}/scenarios', [ScenarioController::class, 'store'])->name('web.scenarios.store');
});

Route::view('/docs', 'docs')->name('docs');
Route::get('/openapi.json', fn () => response(file_get_contents(base_path('docs/openapi.json')), 200, ['Content-Type' => 'application/json']));
