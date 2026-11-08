<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

beforeEach(function (): void {
    $this->withoutVite();
});

it('serves accessible web auth forms and redirects guests to login', function (): void {
    $this->get('/login')->assertOk()->assertSee('Good to see you again.')->assertSee('name="_token"', false);
    $this->get('/register')->assertOk()->assertSee('Create account');
    foreach (['/dashboard', '/devices', '/devices/create', '/analyses', '/analyses/create'] as $path) {
        $this->get($path)->assertRedirect('/login');
    }
});

it('registers a web session without issuing an API token', function (): void {
    $this->get('/register');
    $sessionId = session()->getId();
    $this->post('/register', [
        'name' => 'Student',
        'email' => 'STUDENT@example.com',
        'password' => 'StrongPass1234',
        'password_confirmation' => 'StrongPass1234',
    ])->assertRedirect('/dashboard');

    $user = User::query()->firstOrFail();
    $this->assertAuthenticatedAs($user, 'web');
    expect($user->email)->toBe('student@example.com')
        ->and(Hash::check('StrongPass1234', $user->password))->toBeTrue()
        ->and(session()->getId())->not->toBe($sessionId);
    $this->assertDatabaseCount('personal_access_tokens', 0);
    $this->get('/dashboard')->assertOk()->assertSee('Hello, Student');
});

it('validates web registration without creating a user', function (): void {
    $this->from('/register')->post('/register', [
        'name' => '',
        'email' => 'not-an-email',
        'password' => 'short',
        'password_confirmation' => 'different',
    ])->assertRedirect('/register')->assertSessionHasErrors(['name', 'email', 'password']);
    $this->assertDatabaseCount('users', 0);
    $this->assertGuest('web');
});

it('logs in with normalized email and preserves the session across requests', function (): void {
    $user = User::factory()->create(['email' => 'student@example.com', 'password' => 'StrongPass1234']);
    $this->get('/login');
    $sessionId = session()->getId();
    $response = $this->post('/login', ['email' => ' STUDENT@example.com ', 'password' => 'StrongPass1234'])
        ->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($user, 'web');
    expect(session()->getId())->not->toBe($sessionId);

    $cookie = collect($response->headers->getCookies())->first(fn ($cookie) => $cookie->getName() === config('session.cookie'));
    expect($cookie->isHttpOnly())->toBeTrue()->and($cookie->getSameSite())->toBe('lax');
    $this->withUnencryptedCookie($cookie->getName(), $cookie->getValue());
    Auth::forgetGuards();
    $this->get('/dashboard')->assertOk();
});

it('rejects invalid login types and credentials through the web form', function (mixed $email): void {
    $this->from('/login')->post('/login', ['email' => $email, 'password' => 'invalid'])
        ->assertRedirect('/login')->assertSessionHasErrors('email');
    $this->assertGuest('web');
})->with([
    'unknown email' => ['unknown@example.com'],
    'array email' => [['student@example.com']],
    'object email' => [['address' => 'student@example.com']],
]);

it('rejects a multibyte web password exceeding the bcrypt byte limit', function (): void {
    $password = 'Aa1234'.str_repeat('あ', 22);
    User::factory()->create(['email' => 'student@example.com', 'password' => $password]);
    $this->from('/login')->post('/login', ['email' => 'student@example.com', 'password' => $password.'あ'])
        ->assertRedirect('/login')->assertSessionHasErrors('email');
    $this->assertGuest('web');
});

it('keeps web sessions and API bearer authentication separate', function (): void {
    $user = User::factory()->create(['password' => 'StrongPass1234']);
    $this->post('/login', ['email' => $user->email, 'password' => 'StrongPass1234'])->assertRedirect('/dashboard');
    $this->getJson('/api/v1/me')->assertUnauthorized();
    $this->assertDatabaseCount('personal_access_tokens', 0);
});

it('does not accept an API token as a website session', function (): void {
    $user = User::factory()->create();
    $this->withToken($user->createToken('api-test')->plainTextToken);
    $this->getJson('/api/v1/me')->assertOk();
    $this->get('/dashboard')->assertRedirect('/login');
});

it('logs out and invalidates the website session without revoking API tokens', function (): void {
    $user = User::factory()->create(['password' => 'StrongPass1234']);
    $user->createToken('separate-api-client');
    $this->post('/login', ['email' => $user->email, 'password' => 'StrongPass1234'])->assertRedirect('/dashboard');
    $sessionId = session()->getId();
    $csrfToken = session()->token();

    $this->post('/logout')->assertRedirect('/login');
    $this->assertGuest('web');
    expect(session()->getId())->not->toBe($sessionId)->and(session()->token())->not->toBe($csrfToken);
    $this->assertDatabaseCount('personal_access_tokens', 1);
    $this->get('/dashboard')->assertRedirect('/login');
});

it('shares the normal login rate limit across web and API requests', function (): void {
    for ($attempt = 0; $attempt < 5; $attempt++) {
        $this->post('/login', ['email' => 'rate@example.com', 'password' => 'invalid'])->assertSessionHasErrors('email');
    }
    $this->postJson('/api/v1/auth/login', ['email' => 'RATE@example.com', 'password' => 'invalid'])
        ->assertStatus(429)->assertHeader('Retry-After');
});

it('redirects signed-in users away from guest forms', function (): void {
    $this->actingAs(User::factory()->create(), 'web');
    $this->get('/login')->assertRedirect('/dashboard');
    $this->get('/register')->assertRedirect('/dashboard');
});

it('enforces CSRF when the framework test bypass is disabled', function (): void {
    $this->app->instance('env', 'local');
    $input = ['name' => 'Student', 'email' => 'csrf@example.com', 'password' => 'StrongPass1234', 'password_confirmation' => 'StrongPass1234'];
    $this->post('/register', $input)->assertStatus(419);
    $this->assertDatabaseCount('users', 0);
    $this->withSession(['_token' => 'known-csrf-token'])
        ->post('/register', $input + ['_token' => 'wrong-csrf-token'])->assertStatus(419);
    $this->assertDatabaseCount('users', 0);
    $this->withSession(['_token' => 'known-csrf-token'])
        ->post('/register', $input + ['_token' => 'known-csrf-token'])->assertRedirect('/dashboard');
    $this->assertDatabaseCount('users', 1);
});
