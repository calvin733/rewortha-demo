@extends('layouts.auth')
@section('title', 'Sign in')
@section('content')
    <p class="eyebrow">Sign in</p>
    <h1 class="page-title mt-2">Good to see you again.</h1>
    <p class="mb-5 mt-3 text-sm leading-6 text-text-muted">Open your devices and saved comparisons.</p>

    <x-validation-errors :fields="['email', 'password']" />

    <form method="POST" action="{{ route('login.store') }}" class="space-y-4" data-submit-state>
        @csrf
        <x-field name="email" label="Email address" type="email" autocomplete="email" placeholder="you@example.com" required maxlength="254" />
        <x-field name="password" label="Password" type="password" autocomplete="current-password" required maxlength="72" />
        <button type="submit" class="btn-primary mt-1 w-full" data-loading="Signing in…">
            <span data-button-label>Sign in</span><x-icon name="arrow-right" class="size-4" />
        </button>
    </form>

    <p class="mt-3 flex flex-wrap items-center gap-x-2 text-sm text-text-muted">
        New to ReWortha? <a href="{{ route('register') }}" class="text-link">Create an account</a>
    </p>
@endsection
