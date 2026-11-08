<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)->use(RefreshDatabase::class)->in('Feature');

function reworthaInput(array $overrides = []): array
{
    return array_replace(['repair_cost' => 700000, 'repair_lifetime_months' => 12, 'replacement_cost' => 8000000, 'replacement_lifetime_months' => 48], $overrides);
}

function reworthaAuth(?User $user = null): User
{
    $user ??= User::factory()->create();
    test()->withToken($user->createToken('test')->plainTextToken);
    app('auth')->forgetGuards();

    return $user;
}
