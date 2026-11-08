@extends('layouts.auth')
@section('title', 'Create an account')
@section('content')
    <h1 class="page-title">Create an account</h1>
    <p class="mb-5 mt-3 text-sm leading-6 text-text-muted">Save devices and return to their comparisons later.</p>

    <x-validation-errors :fields="['name', 'email', 'password', 'password_confirmation']" />

    <form method="POST" action="{{ route('register.store') }}" class="space-y-4" data-submit-state>
        @csrf
        <x-field name="name" label="Full name" autocomplete="name" required maxlength="100" />
        <x-field name="email" label="Email address" type="email" autocomplete="email" placeholder="you@example.com" required maxlength="254" />
        <x-field name="password" label="Password" type="password" autocomplete="new-password" required minlength="12" maxlength="72" help="At least 12 characters, with an uppercase letter, a lowercase letter, and a number." />
        <x-field name="password_confirmation" label="Confirm password" type="password" autocomplete="new-password" required minlength="12" maxlength="72" />
        <button type="submit" class="btn-primary mt-1 w-full" data-loading="Creating account…">
            <span data-button-label>Create account</span><x-icon name="arrow-right" class="size-4" />
        </button>
    </form>

    <p class="mt-3 flex flex-wrap items-center gap-x-2 text-sm text-text-muted">
        Already have an account? <a href="{{ route('login') }}" class="text-link">Sign in</a>
    </p>
@endsection
