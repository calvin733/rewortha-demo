<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

it('exposes safe liveness and database readiness', function (): void {
    $this->getJson('/api/v1/health')->assertOk()->assertJsonPath('data.status', 'ok');
    $this->getJson('/api/v1/health?check=database')->assertOk()->assertJsonPath('data.database', 'ok');
});

it('returns 503 without database details when readiness fails', function (): void {
    DB::shouldReceive('select')->once()->with('select 1')->andThrow(new RuntimeException('secret connection information'));
    $this->getJson('/api/v1/health?check=database')->assertStatus(503)->assertExactJson(['data' => ['status' => 'unavailable', 'service' => 'ReWortha', 'database' => 'unavailable']]);
});

it('sanitizes unexpected API exceptions even when debug is enabled', function (): void {
    config(['app.debug' => true]);
    Route::get('api/v1/test-error', function (): never {
        throw new RuntimeException('private-stack-and-credentials');
    });
    $this->getJson('/api/v1/test-error')->assertStatus(500)->assertExactJson(['message' => 'An unexpected error occurred.']);
});
