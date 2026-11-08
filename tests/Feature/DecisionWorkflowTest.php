<?php

use App\Models\Analysis;
use App\Models\Device;
use App\Models\Scenario;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

it('creates updates and logically archives owned devices with protected ownership', function (): void {
    $user = reworthaAuth();
    $deviceId = $this->postJson('/api/v1/devices', ['name' => 'Study laptop', 'category' => 'laptop', 'user_id' => 999, 'archived_at' => now()->toISOString()])->assertCreated()->json('data.id');
    $device = Device::query()->findOrFail($deviceId);
    expect($device->user_id)->toBe($user->id)->and($device->archived_at)->toBeNull();
    $this->patchJson("/api/v1/devices/{$deviceId}", ['name' => 'Updated laptop'])->assertOk()->assertJsonPath('data.name', 'Updated laptop');
    $this->deleteJson("/api/v1/devices/{$deviceId}")->assertNoContent();
    $archivedAt = $device->fresh()->archived_at;
    $this->deleteJson("/api/v1/devices/{$deviceId}")->assertNoContent();
    expect($device->fresh()->archived_at->equalTo($archivedAt))->toBeTrue();
    $this->getJson('/api/v1/devices')->assertJsonCount(0, 'data');
    $this->getJson('/api/v1/devices?archived=archived')->assertJsonCount(1, 'data');
    $this->getJson("/api/v1/devices/{$deviceId}")->assertOk();
    $this->patchJson("/api/v1/devices/{$deviceId}", ['name' => 'Cannot edit'])->assertStatus(409);
});

it('saves immutable analyses and independent what-if scenarios with baseline comparison', function (): void {
    $user = reworthaAuth();
    $device = Device::factory()->for($user)->create(['name' => 'Original laptop']);
    $analysisId = $this->postJson("/api/v1/devices/{$device->id}/analyses", reworthaInput())->assertCreated()->assertJsonPath('data.result_snapshot.classification', 'FAVOR_REPAIR')->json('data.id');
    $before = Analysis::query()->findOrFail($analysisId)->getRawOriginal();
    $device->update(['name' => 'Renamed laptop']);
    $scenarioId = $this->postJson("/api/v1/analyses/{$analysisId}/scenarios", ['name' => 'Shorter repair life', 'repair_cost' => 1200000, 'repair_lifetime_months' => 6])->assertCreated()->assertJsonPath('data.classification', 'FAVOR_REPLACE')->assertJsonPath('data.input_snapshot.replacement_cost', 8000000)->json('data.id');
    $this->getJson("/api/v1/scenarios/{$scenarioId}")->assertOk()->assertJsonPath('data.baseline.input_snapshot.repair_cost', 700000)->assertJsonPath('data.baseline.device_snapshot.name', 'Original laptop');
    $this->postJson("/api/v1/analyses/{$analysisId}/scenarios", ['repair_cost' => 800000])->assertCreated()->assertJsonPath('data.input_snapshot.repair_lifetime_months', 12);
    expect(Analysis::query()->findOrFail($analysisId)->getRawOriginal())->toBe($before);
    $this->getJson('/api/v1/analyses')->assertJsonPath('meta.total', 1);
    $this->getJson("/api/v1/analyses/{$analysisId}")->assertJsonPath('data.scenarios_count', 2);
    $this->getJson("/api/v1/analyses/{$analysisId}/scenarios?per_page=1")->assertJsonPath('meta.total', 2)->assertJsonCount(1, 'data');
    $this->postJson("/api/v1/analyses/{$analysisId}/scenarios", ['name' => 'No inputs'])->assertUnprocessable();
    $this->postJson("/api/v1/analyses/{$analysisId}/scenarios", ['repair_cost' => 700000])->assertUnprocessable();
    $this->deleteJson("/api/v1/devices/{$device->id}")->assertNoContent();
    $this->postJson("/api/v1/devices/{$device->id}/analyses", reworthaInput())->assertStatus(409);
    $this->postJson("/api/v1/analyses/{$analysisId}/scenarios", ['repair_cost' => 500000])->assertStatus(409);
    $this->getJson("/api/v1/analyses/{$analysisId}")->assertOk();
});

it('prevents cross-owner access for every domain endpoint', function (): void {
    $owner = reworthaAuth();
    $device = Device::factory()->for($owner)->create();
    $analysisId = $this->postJson("/api/v1/devices/{$device->id}/analyses", reworthaInput())->json('data.id');
    $scenarioId = $this->postJson("/api/v1/analyses/{$analysisId}/scenarios", ['repair_cost' => 600000])->json('data.id');
    reworthaAuth(User::factory()->create());
    $this->getJson("/api/v1/devices/{$device->id}")->assertNotFound();
    $this->patchJson("/api/v1/devices/{$device->id}", ['name' => 'Stolen'])->assertNotFound();
    $this->deleteJson("/api/v1/devices/{$device->id}")->assertNotFound();
    $this->postJson("/api/v1/devices/{$device->id}/analyses", reworthaInput())->assertNotFound();
    $this->getJson("/api/v1/analyses/{$analysisId}")->assertNotFound();
    $this->getJson("/api/v1/analyses/{$analysisId}/scenarios")->assertNotFound();
    $this->postJson("/api/v1/analyses/{$analysisId}/scenarios", ['repair_cost' => 123])->assertNotFound();
    $this->getJson("/api/v1/scenarios/{$scenarioId}")->assertNotFound();
    $this->getJson('/api/v1/devices')->assertJsonPath('meta.total', 0);
    $this->getJson('/api/v1/analyses')->assertJsonPath('meta.total', 0);
});

it('checks ownership before validating writes to another users resources', function (): void {
    $owner = reworthaAuth();
    $device = Device::factory()->for($owner)->create();
    $analysisId = $this->postJson("/api/v1/devices/{$device->id}/analyses", reworthaInput())
        ->assertCreated()
        ->json('data.id');
    reworthaAuth(User::factory()->create());

    $this->patchJson("/api/v1/devices/{$device->id}", ['name' => []])->assertNotFound();
    $this->postJson("/api/v1/devices/{$device->id}/analyses", ['repair_cost' => -1])->assertNotFound();
    $this->postJson("/api/v1/analyses/{$analysisId}/scenarios", [])->assertNotFound();
});

it('uses the specific device ability for each write action', function (string $method, string $ability): void {
    $user = reworthaAuth();
    $device = Device::factory()->for($user)->create(['name' => 'Protected laptop']);
    Gate::before(function (User $user, string $requestedAbility) use ($ability): ?Response {
        return $requestedAbility === $ability ? Response::denyAsNotFound() : null;
    });

    $this->getJson("/api/v1/devices/{$device->id}")->assertOk();
    $this->json($method, "/api/v1/devices/{$device->id}", ['name' => 'Should not change'])->assertNotFound();
    $this->assertDatabaseHas('devices', [
        'id' => $device->id,
        'name' => 'Protected laptop',
        'archived_at' => null,
    ]);
})->with([
    'edit checks update' => ['PATCH', 'update'],
    'archive checks delete' => ['DELETE', 'delete'],
]);

it('validates device fields and bounded pagination', function (): void {
    $user = reworthaAuth();
    $this->postJson('/api/v1/devices', ['name' => '', 'category' => 'spaceship', 'purchase_year' => now()->year + 1])->assertUnprocessable()->assertJsonValidationErrors(['name', 'category', 'purchase_year']);
    Device::factory()->count(16)->for($user)->create();
    $this->getJson('/api/v1/devices?per_page=5&page=2')->assertOk()->assertJsonCount(5, 'data')->assertJsonStructure(['data', 'links', 'meta'])->assertJsonPath('meta.total', 16);
    $this->getJson('/api/v1/devices?per_page=101')->assertUnprocessable();
    $this->getJson('/api/v1/devices/99999999999999999999999999')->assertNotFound();
});

it('rejects unchanged scenarios regardless of JSON object key order', function (): void {
    $user = reworthaAuth();
    $device = Device::factory()->for($user)->create();
    $id = $this->postJson("/api/v1/devices/{$device->id}/analyses", reworthaInput())->assertCreated()->json('data.id');
    $snapshot = Analysis::query()->findOrFail($id)->input_snapshot;
    DB::table('analyses')->where('id', $id)->update(['input_snapshot' => json_encode(array_reverse($snapshot, true), JSON_THROW_ON_ERROR)]);
    $this->postJson("/api/v1/analyses/{$id}/scenarios", ['repair_cost' => 700000])->assertUnprocessable()->assertJsonValidationErrors('scenario');
    $this->assertDatabaseCount('scenarios', 0);
});

it('rejects invalid analysis inputs', function (string $field, mixed $value): void {
    $user = reworthaAuth();
    $device = Device::factory()->for($user)->create();
    $this->postJson("/api/v1/devices/{$device->id}/analyses", reworthaInput([$field => $value]))->assertUnprocessable()->assertJsonValidationErrors($field);
})->with([['repair_cost', -1], ['repair_cost', '700000'], ['repair_cost', 1.5], ['repair_lifetime_months', 0], ['replacement_lifetime_months', 241], ['replacement_cost', 1000000001], ['repair_monthly_operating_cost', null]]);

it('blocks historical mutation through Eloquent', function (): void {
    $user = reworthaAuth();
    $device = Device::factory()->for($user)->create();
    $id = $this->postJson("/api/v1/devices/{$device->id}/analyses", reworthaInput())->json('data.id');
    expect(fn () => Analysis::query()->findOrFail($id)->update(['classification' => 'FAVOR_REPLACE']))->toThrow(LogicException::class);
    expect(fn () => Analysis::query()->findOrFail($id)->delete())->toThrow(LogicException::class);
    $scenarioId = $this->postJson("/api/v1/analyses/{$id}/scenarios", ['repair_cost' => 600000])->assertCreated()->json('data.id');
    expect(fn () => Scenario::query()->findOrFail($scenarioId)->update(['classification' => 'FAVOR_REPLACE']))->toThrow(LogicException::class);
    expect(fn () => Scenario::query()->findOrFail($scenarioId)->delete())->toThrow(LogicException::class);
    $this->patchJson("/api/v1/analyses/{$id}", [])->assertStatus(405);
});

it('requires each upfront cost and lifetime when creating an analysis', function (string $field): void {
    $user = reworthaAuth();
    $device = Device::factory()->for($user)->create();
    $input = reworthaInput();
    unset($input[$field]);

    $this->postJson("/api/v1/devices/{$device->id}/analyses", $input)
        ->assertUnprocessable()
        ->assertJsonValidationErrors($field);
})->with(['repair_cost', 'repair_lifetime_months', 'replacement_cost', 'replacement_lifetime_months']);

it('preserves strict validation for partial scenario inputs', function (string $field, mixed $value): void {
    $user = reworthaAuth();
    $device = Device::factory()->for($user)->create();
    $analysisId = $this->postJson("/api/v1/devices/{$device->id}/analyses", reworthaInput())
        ->assertCreated()
        ->json('data.id');

    $this->postJson("/api/v1/analyses/{$analysisId}/scenarios", [$field => $value])
        ->assertUnprocessable()
        ->assertJsonValidationErrors($field);
    $this->assertDatabaseCount('scenarios', 0);
})->with([
    ['repair_cost', '700000'],
    ['repair_lifetime_months', 0],
    ['replacement_cost', 1000000001],
    ['replacement_lifetime_months', 241],
    ['repair_monthly_operating_cost', null],
    ['replacement_monthly_operating_cost', 10000001],
]);
