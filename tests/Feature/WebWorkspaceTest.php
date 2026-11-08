<?php

use App\Models\Analysis;
use App\Models\Device;
use App\Models\Scenario;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;

beforeEach(function (): void {
    $this->withoutVite();
});

it('completes device CRUD and logical archive through web forms', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user, 'web');
    $this->get('/dashboard')->assertOk()->assertSee('Every device has a story.');
    $this->get('/devices/create')->assertOk();
    $this->post('/devices', [
        'name' => 'Study laptop', 'category' => 'laptop', 'purchase_year' => '2022',
        'user_id' => 999, 'archived_at' => '2020-01-01', 'brand' => '',
    ])->assertRedirect();
    $device = Device::query()->firstOrFail();
    expect($device->user_id)->toBe($user->id)->and($device->purchase_year)->toBe(2022)->and($device->archived_at)->toBeNull();
    $this->get("/devices/{$device->id}")->assertOk()->assertSee('Study laptop');
    $this->get("/devices/{$device->id}/edit")->assertOk();
    $this->patch("/devices/{$device->id}", ['name' => 'Renamed laptop', 'purchase_year' => '2023'])
        ->assertRedirect("/devices/{$device->id}");
    expect($device->fresh()->name)->toBe('Renamed laptop')->and($device->fresh()->purchase_year)->toBe(2023);
    $this->delete("/devices/{$device->id}")->assertRedirect('/devices');
    $archivedAt = $device->fresh()->archived_at;
    $this->delete("/devices/{$device->id}")->assertRedirect('/devices');
    expect($device->fresh()->archived_at->equalTo($archivedAt))->toBeTrue();
    $this->get('/devices')->assertDontSee('Renamed laptop');
    $this->get('/devices?archived=archived')->assertSee('Renamed laptop');
    $this->get("/devices/{$device->id}")->assertOk()->assertSee('This device is archived.');
    $this->get("/devices/{$device->id}/edit")->assertStatus(409);
    $this->patch("/devices/{$device->id}", ['name' => 'Changed'])->assertStatus(409);
});

it('handles invalid web input and old array values without rendering errors', function (): void {
    $this->actingAs(User::factory()->create(), 'web');
    $response = $this->from('/devices/create')->post('/devices', ['name' => ['unexpected'], 'category' => 'unknown', 'purchase_year' => '2022.5'])
        ->assertRedirect('/devices/create')->assertSessionHasErrors(['name', 'category', 'purchase_year']);
    $cookie = collect($response->headers->getCookies())->first(fn ($cookie) => $cookie->getName() === config('session.cookie'));
    $this->withUnencryptedCookie($cookie->getName(), $cookie->getValue());
    $this->get('/devices/create')->assertOk()->assertSee('A few details need another look.')
        ->assertSee('The name field must be a string.')->assertSee('value="2022.5"', false);
    $this->assertDatabaseCount('devices', 0);
});

it('escapes untrusted device names and notes in the website', function (): void {
    $user = User::factory()->create();
    $device = Device::factory()->for($user)->create(['name' => '<script>alert(1)</script>', 'condition_notes' => '<img src=x onerror=alert(1)>']);
    $this->actingAs($user, 'web')->get("/devices/{$device->id}")
        ->assertOk()->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
        ->assertDontSee('<script>alert(1)</script>', false)->assertDontSee('<img src=x onerror=alert(1)>', false);
});

it('shows only the current users devices and rejects cross-owner device actions', function (): void {
    $owner = User::factory()->create();
    $device = Device::factory()->for($owner)->create(['name' => 'Private device']);
    $this->actingAs(User::factory()->create(), 'web');
    $this->get('/dashboard')->assertDontSee('Private device');
    $this->get('/devices')->assertDontSee('Private device');
    $this->get("/devices/{$device->id}")->assertNotFound();
    $this->get("/devices/{$device->id}/edit")->assertNotFound();
    $this->patch("/devices/{$device->id}", ['name' => []])->assertNotFound();
    $this->delete("/devices/{$device->id}")->assertNotFound();
    expect($device->fresh()->archived_at)->toBeNull();
    $this->get('/devices/9999999999999999999999999')->assertNotFound();
});

it('keeps update and archive abilities distinct from view on the website', function (string $method, string $ability): void {
    $user = User::factory()->create();
    $device = Device::factory()->for($user)->create(['name' => 'Protected device']);
    $this->actingAs($user, 'web');
    Gate::before(fn (User $user, string $requested): ?Response => $requested === $ability ? Response::denyAsNotFound() : null);
    $this->get("/devices/{$device->id}")->assertOk();
    $this->json($method, "/devices/{$device->id}", ['name' => 'Changed'])->assertNotFound();
    $this->assertDatabaseHas('devices', ['id' => $device->id, 'name' => 'Protected device', 'archived_at' => null]);
})->with([['PATCH', 'update'], ['DELETE', 'delete']]);

it('guides users to add a device before starting an analysis', function (): void {
    $this->actingAs(User::factory()->create(), 'web')->get('/analyses/create')
        ->assertRedirect('/devices/create')->assertSessionHas('status');
});

it('converts validated form numbers and saves the existing engine result', function (): void {
    $user = User::factory()->create();
    $device = Device::factory()->for($user)->create(['name' => 'Original laptop']);
    $this->actingAs($user, 'web');
    $this->get("/analyses/create?device_id={$device->id}")->assertOk()->assertSee('Repair or replace?');
    $input = array_map('strval', reworthaInput());
    $this->post('/analyses', $input + ['device_id' => (string) $device->id, 'repair_monthly_operating_cost' => '', 'replacement_monthly_operating_cost' => ''])
        ->assertRedirect();
    $analysis = Analysis::query()->firstOrFail();
    expect($analysis->input_snapshot)->toBe(reworthaInput(['repair_monthly_operating_cost' => 0, 'replacement_monthly_operating_cost' => 0]))
        ->and($analysis->classification)->toBe('FAVOR_REPAIR')
        ->and($analysis->result_snapshot['repair_monthly_cost'])->toBe(58333)
        ->and($analysis->result_snapshot['replacement_monthly_cost'])->toBe(166667);
    $this->get("/analyses/{$analysis->id}")->assertOk()->assertSee('58.333')->assertSee('166.667')->assertSee('What if things change?');
    $this->get('/analyses')->assertOk()->assertSee('Original laptop');
    $this->get('/analyses?device_id=')->assertOk()->assertSee('Original laptop');
    $this->get("/devices/{$device->id}")->assertSee("Comparison #{$analysis->id}");
});

it('rejects invalid analysis form numbers before casting', function (string $field, mixed $value): void {
    $user = User::factory()->create();
    $device = Device::factory()->for($user)->create();
    $this->actingAs($user, 'web')->from('/analyses/create');
    $input = array_replace(array_map('strval', reworthaInput()), ['device_id' => (string) $device->id], [$field => $value]);
    $this->post('/analyses', $input)->assertRedirect('/analyses/create')->assertSessionHasErrors($field);
    $this->assertDatabaseCount('analyses', 0);
})->with([
    ['repair_cost', '1.5'],
    ['repair_cost', '1e3'],
    ['replacement_cost', '8000000x'],
    ['repair_lifetime_months', '0'],
    ['replacement_lifetime_months', '241'],
    ['repair_monthly_operating_cost', '-1'],
    ['replacement_monthly_operating_cost', '10000001'],
    ['repair_cost', ['unexpected']],
    ['device_id', '9999999999999999999999999'],
]);

it('rejects choosing another users device for a web analysis', function (): void {
    $device = Device::factory()->create();
    $this->actingAs(User::factory()->create(), 'web')->from('/analyses/create');
    $this->post('/analyses', array_map('strval', reworthaInput()) + ['device_id' => (string) $device->id])
        ->assertSessionHasErrors('device_id');
    $this->assertDatabaseCount('analyses', 0);
});

it('persists independent what-if scenarios and keeps original history unchanged', function (): void {
    $user = User::factory()->create();
    $device = Device::factory()->for($user)->create(['name' => 'Original laptop']);
    $this->actingAs($user, 'web')->post('/analyses', array_map('strval', reworthaInput()) + ['device_id' => (string) $device->id]);
    $analysis = Analysis::query()->firstOrFail();
    $baseline = $analysis->getRawOriginal();
    $device->update(['name' => 'Current laptop name']);

    $this->post("/analyses/{$analysis->id}/scenarios", [
        'name' => 'Shorter repair life', 'repair_cost' => '1200000', 'repair_lifetime_months' => '6',
        'replacement_cost' => '', 'replacement_lifetime_months' => '', 'repair_monthly_operating_cost' => '',
    ])->assertRedirect();
    $scenario = Scenario::query()->firstOrFail();
    expect($scenario->classification)->toBe('FAVOR_REPLACE')
        ->and($scenario->input_snapshot['repair_cost'])->toBe(1200000)
        ->and($scenario->input_snapshot['replacement_lifetime_months'])->toBe(48)
        ->and($analysis->fresh()->getRawOriginal())->toBe($baseline);
    $this->get("/analyses/{$analysis->id}?scenario={$scenario->id}")->assertOk()->assertSee('Shorter repair life')->assertSee('Original laptop');

    $this->post("/analyses/{$analysis->id}/scenarios", ['repair_cost' => '0'])->assertRedirect();
    $second = Scenario::query()->latest('id')->firstOrFail();
    expect($second->input_snapshot['repair_cost'])->toBe(0)->and($second->input_snapshot['repair_lifetime_months'])->toBe(12);

    $this->from("/analyses/{$analysis->id}")->post("/analyses/{$analysis->id}/scenarios", ['repair_cost' => '700000'])
        ->assertSessionHasErrors('scenario');
    $this->post("/analyses/{$analysis->id}/scenarios", ['name' => 'No changes', 'repair_cost' => ''])->assertSessionHasErrors('scenario');
    $this->assertDatabaseCount('scenarios', 2);
});

it('protects analyses scenarios and history from cross-user access', function (): void {
    $owner = User::factory()->create();
    $device = Device::factory()->for($owner)->create(['name' => 'Private history']);
    $this->actingAs($owner, 'web')->post('/analyses', array_map('strval', reworthaInput()) + ['device_id' => (string) $device->id]);
    $analysis = Analysis::query()->firstOrFail();
    $this->post("/analyses/{$analysis->id}/scenarios", ['repair_cost' => '600000']);
    $this->actingAs(User::factory()->create(), 'web');
    $this->get('/analyses')->assertOk()->assertDontSee('Private history');
    $this->get('/dashboard')->assertDontSee('Private history');
    $this->get("/analyses/{$analysis->id}")->assertNotFound();
    $this->post("/analyses/{$analysis->id}/scenarios", [])->assertNotFound();
});

it('rejects selecting a scenario from a different original analysis', function (): void {
    $user = User::factory()->create();
    $device = Device::factory()->for($user)->create();
    $this->actingAs($user, 'web');
    $input = array_map('strval', reworthaInput()) + ['device_id' => (string) $device->id];
    $this->post('/analyses', $input);
    $first = Analysis::query()->firstOrFail();
    $this->post("/analyses/{$first->id}/scenarios", ['repair_cost' => '600000']);
    $scenario = Scenario::query()->firstOrFail();
    $this->post('/analyses', $input);
    $second = Analysis::query()->latest('id')->firstOrFail();
    $this->get("/analyses/{$second->id}?scenario={$scenario->id}")->assertNotFound();
    $this->get("/analyses/{$second->id}?scenario[]=1")->assertNotFound();
});

it('keeps archived device history readable but rejects new results', function (): void {
    $user = User::factory()->create();
    $device = Device::factory()->for($user)->create();
    $this->actingAs($user, 'web');
    $input = array_map('strval', reworthaInput()) + ['device_id' => (string) $device->id];
    $this->post('/analyses', $input);
    $analysis = Analysis::query()->firstOrFail();
    $this->delete("/devices/{$device->id}")->assertRedirect('/devices');
    $this->post('/analyses', $input)->assertStatus(409);
    $this->post("/analyses/{$analysis->id}/scenarios", ['repair_cost' => '600000'])->assertStatus(409);
    $this->get("/analyses/{$analysis->id}")->assertOk()->assertSee('This device is archived.');
    $this->get('/analyses')->assertOk();
});
