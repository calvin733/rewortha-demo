<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Compare estimated monthly costs for repairing or replacing your devices.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Overview') · ReWortha</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-dvh flex-col">
    <a href="#main-content" class="sr-only z-50 rounded-control bg-surface p-3 focus:not-sr-only focus:fixed focus:start-4 focus:top-4">Skip to content</a>

    <header class="border-b border-border bg-surface">
        <div class="shell-container workspace-bar">
            <x-brand />

            <nav class="workspace-nav" aria-label="Main navigation">
                @foreach([
                    ['dashboard', 'dashboard', 'Overview', 'dashboard'],
                    ['web.devices.index', 'web.devices.*', 'Devices', 'devices'],
                    ['web.analyses.index', 'web.analyses.*', 'History', 'history'],
                ] as [$routeName, $pattern, $label, $icon])
                    <a href="{{ route($routeName) }}" class="nav-item"
                        @if(request()->routeIs($pattern)) aria-current="{{ request()->routeIs($routeName) ? 'page' : 'location' }}" @endif>
                        <x-icon :name="$icon" class="size-4" />
                        {{ $label }}
                    </a>
                @endforeach
            </nav>

            <div class="workspace-account">
                <span class="hidden max-w-32 truncate text-sm font-medium text-text-muted lg:block">{{ auth('web')->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}" data-submit-state>
                    @csrf
                    <button type="submit" class="btn-ghost whitespace-nowrap px-2 sm:px-3" data-loading="Signing out…">
                        <x-icon name="logout" class="size-4" />
                        <span data-button-label>Sign out</span>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main id="main-content" tabindex="-1" class="shell-container flex-1 py-6 sm:py-9">
        <p class="notice notice-warning mb-5" role="status" data-offline-notice hidden>You’re offline. Reconnect before submitting your changes.</p>
        <p class="sr-only" role="status" data-progress-status></p>

        @if(session('status'))
            <div class="notice notice-success mb-5" role="status">
                <x-icon name="check" class="mt-0.5 size-5 text-success" />
                <span class="min-w-0">{{ session('status') }}</span>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="shell-container">
        <div class="flex flex-wrap items-center justify-between gap-x-5 border-t border-border py-2 text-sm text-text-muted">
            <span>ReWortha</span>
            <a href="{{ route('docs') }}" class="text-link">API documentation<x-icon name="arrow-right" class="size-4" /></a>
        </div>
    </footer>
</body>
</html>
