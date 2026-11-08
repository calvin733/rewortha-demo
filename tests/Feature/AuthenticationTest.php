<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

it('registers normalized email and stores only a hashed password and token', function (): void {
    $response = $this->postJson('/api/v1/auth/register', ['name' => 'Mei', 'email' => 'MEI@example.com', 'password' => 'StrongPass1234', 'password_confirmation' => 'StrongPass1234'])->assertCreated()->assertJsonPath('data.user.email', 'mei@example.com');
    $user = User::query()->firstOrFail();
    expect(Hash::check('StrongPass1234', $user->password))->toBeTrue()->and($user->tokens()->first()->token)->not->toBe($response->json('data.token'));
    $this->withToken($response->json('data.token'))->getJson('/api/v1/me')->assertOk()->assertJsonMissingPath('data.password');
});

it('logs in and revokes only the current token on logout', function (): void {
    $user = User::factory()->create(['password' => 'StrongPass1234']);
    $otherToken = $user->createToken('other')->plainTextToken;
    $token = $this->postJson('/api/v1/auth/login', ['email' => $user->email, 'password' => 'StrongPass1234'])->assertOk()->json('data.token');
    $this->withToken($token)->postJson('/api/v1/auth/logout')->assertNoContent();
    app('auth')->forgetGuards();
    $this->getJson('/api/v1/me')->assertUnauthorized();
    app('auth')->forgetGuards();
    $this->withToken($otherToken)->getJson('/api/v1/me')->assertOk();
});

it('rejects invalid credentials with a generic response', function (): void {
    $this->postJson('/api/v1/auth/login', ['email' => 'unknown@example.com', 'password' => 'invalid'])->assertUnprocessable()->assertJsonPath('errors.email.0', 'The provided credentials are incorrect.');
});

it('rejects non-string login emails with validation errors', function (mixed $email): void {
    $this->postJson('/api/v1/auth/login', [
        'email' => $email,
        'password' => 'StrongPass1234',
    ])->assertUnprocessable()->assertJsonValidationErrors('email');
})->with([
    'array' => [['mei@example.com']],
    'object' => [['address' => 'mei@example.com']],
    'number' => [123],
    'null' => [null],
]);

it('normalizes the login email independently of registration', function (): void {
    User::factory()->create(['email' => 'mei@example.com', 'password' => 'StrongPass1234']);

    $this->postJson('/api/v1/auth/login', [
        'email' => '  MEI@example.com  ',
        'password' => 'StrongPass1234',
    ])->assertOk()->assertJsonPath('data.user.email', 'mei@example.com');
});

it('handles concurrent email registration after validation', function (): void {
    User::creating(function (User $user): void {
        DB::table('users')->insert(['name' => 'Concurrent request', 'email' => $user->email, 'password' => $user->password]);
    });
    $this->postJson('/api/v1/auth/register', ['name' => 'Mei', 'email' => 'race@example.com', 'password' => 'StrongPass1234', 'password_confirmation' => 'StrongPass1234'])->assertUnprocessable()->assertJsonValidationErrors('email');
});

it('rejects duplicate email and weak or overlong passwords', function (): void {
    User::factory()->create(['email' => 'mei@example.com']);
    $this->postJson('/api/v1/auth/register', ['name' => 'Mei', 'email' => 'MEI@example.com', 'password' => 'short', 'password_confirmation' => 'short'])->assertUnprocessable()->assertJsonValidationErrors(['email', 'password']);
    $password = str_repeat('あ', 24).'Aa12';
    $this->postJson('/api/v1/auth/register', ['name' => 'Mei', 'email' => 'new@example.com', 'password' => $password, 'password_confirmation' => $password])->assertUnprocessable()->assertJsonValidationErrors('password');
});

it('requires authentication even without an Accept header', function (): void {
    $this->get('/api/v1/devices')->assertUnauthorized()->assertExactJson(['message' => 'Unauthenticated.']);
});

it('rejects expired tokens', function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('expired', ['*'], now()->subMinute())->plainTextToken;
    $this->withToken($token)->getJson('/api/v1/me')->assertUnauthorized();
});

it('rate limits repeated login attempts', function (): void {
    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/api/v1/auth/login', ['email' => 'rate@example.com', 'password' => 'invalid'])->assertUnprocessable();
    }
    $this->postJson('/api/v1/auth/login', ['email' => 'rate@example.com', 'password' => 'invalid'])->assertStatus(429)->assertHeader('Retry-After');
});

it('shares the login account limit across email letter cases', function (): void {
    for ($attempt = 0; $attempt < 5; $attempt++) {
        $this->postJson('/api/v1/auth/login', ['email' => 'rate@example.com', 'password' => 'invalid'])->assertUnprocessable();
    }

    $this->postJson('/api/v1/auth/login', ['email' => 'RATE@example.com', 'password' => 'invalid'])
        ->assertStatus(429)
        ->assertHeader('Retry-After');
});

it('limits login requests by IP across different email addresses', function (): void {
    for ($attempt = 0; $attempt < 20; $attempt++) {
        $this->postJson('/api/v1/auth/login', ['email' => "attempt{$attempt}@example.com", 'password' => 'invalid'])->assertUnprocessable();
    }

    $this->postJson('/api/v1/auth/login', ['email' => 'next@example.com', 'password' => 'invalid'])
        ->assertStatus(429)
        ->assertHeader('Retry-After');
});
