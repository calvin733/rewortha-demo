<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function loginForm(): View
    {
        return view('auth.login');
    }

    public function registerForm(): View
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        try {
            $user = DB::transaction(fn (): User => User::query()->create(
                $request->safe()->only(['name', 'email', 'password']),
            ));
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['email' => 'The email has already been taken.']);
        }
        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('status', 'Welcome to ReWortha. Let’s give your devices a second thought.');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        if (strlen($request->validated('password')) > 72 ||
            ! Auth::guard('web')->attempt($request->safe()->only(['email', 'password']))) {
            throw ValidationException::withMessages(['email' => 'The provided credentials are incorrect.']);
        }
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'))->with('status', 'Welcome back. Your devices are right where you left them.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'You have been signed out.');
    }
}
