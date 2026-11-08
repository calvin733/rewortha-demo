<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') · ReWortha</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-dvh flex-col">
    <header class="border-b border-border bg-surface">
        <div class="shell-container flex min-h-16 items-center"><x-brand /></div>
    </header>

    <main class="shell-container flex flex-1 items-start py-8 sm:py-12">
        <section class="surface mx-auto w-full max-w-xl p-5 sm:p-7" aria-labelledby="error-title">
            <p class="eyebrow">Error @yield('code')</p>
            <h1 id="error-title" class="page-title mt-2">@yield('title')</h1>
            <p class="mt-3 text-sm leading-6 text-text-muted">@yield('message')</p>
            <a href="{{ route('home') }}" class="btn-primary mt-5">Back to ReWortha<x-icon name="arrow-right" class="size-4" /></a>
        </section>
    </main>

    <footer class="shell-container">
        <div class="border-t border-border py-3 text-sm text-text-muted">ReWortha</div>
    </footer>
</body>
</html>
