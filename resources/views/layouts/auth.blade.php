<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Compare the estimated monthly cost of repairing or replacing a device.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') · ReWortha</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-dvh flex-col">
    <a href="#main-content" class="sr-only z-50 rounded-control bg-surface p-3 focus:not-sr-only focus:fixed focus:start-4 focus:top-4">Skip to content</a>

    <header class="border-b border-border bg-surface">
        <div class="shell-container flex min-h-16 items-center">
            <x-brand />
        </div>
    </header>

    <main id="main-content" tabindex="-1" class="shell-container flex flex-1 items-center py-5 sm:py-9 lg:py-12">
        <div class="auth-layout mx-auto w-full max-w-5xl">
            <aside class="auth-intro" aria-label="About ReWortha">
                <p class="eyebrow text-primary">Repair or replace</p>
                <p class="mt-2 max-w-[17ch] text-xl font-semibold leading-tight tracking-tight md:mt-4 md:text-3xl">Compare what each option costs per month.</p>
                <p class="mt-2 max-w-prose text-sm leading-6 text-text-muted md:mt-4 md:text-base">Use your cost and lifespan estimates to compare both options for a device.</p>
                <dl class="mt-7 hidden border-y border-control-border/40 md:block">
                    <div class="flex items-start gap-3 border-b border-control-border/40 py-4">
                        <x-icon name="wrench" class="mt-0.5 text-primary" />
                        <div><dt class="text-sm font-semibold">Repair</dt><dd class="mt-0.5 text-sm text-text-muted">Repair cost and expected use after repair.</dd></div>
                    </div>
                    <div class="flex items-start gap-3 py-4">
                        <x-icon name="devices" class="mt-0.5 text-primary" />
                        <div><dt class="text-sm font-semibold">Replacement</dt><dd class="mt-0.5 text-sm text-text-muted">Purchase price and expected use of the replacement.</dd></div>
                    </div>
                </dl>
            </aside>

            <div class="auth-form">
                <p class="notice notice-warning mb-5" role="status" data-offline-notice hidden>You’re offline. Reconnect before submitting your changes.</p>
                <p class="sr-only" role="status" data-progress-status></p>

                @if(session('status'))
                    <p class="notice notice-success mb-5" role="status">{{ session('status') }}</p>
                @endif

                @yield('content')
            </div>
        </div>
    </main>

    <footer class="shell-container">
        <div class="border-t border-border py-3 text-sm text-text-muted">ReWortha</div>
    </footer>
</body>
</html>
