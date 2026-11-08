<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>API documentation · ReWortha</title>
    @if(is_file(public_path('build/manifest.json')))
        @fonts
        @vite('resources/css/app.css')
    @endif
    <link rel="stylesheet" href="{{ asset('vendor/swagger-ui/swagger-ui.css') }}">
    <style>
        body {
            margin: 0;
            background: var(--color-background, #f5f7f6);
            color: var(--color-foreground, #1d2b2c);
            font-family: var(--font-sans, system-ui, sans-serif);
        }
        .swagger-ui .topbar { display: none; }
        .swagger-ui .wrapper { padding: 0; }
        .swagger-ui .info { margin: 24px 0; }
        .docs-content { overflow-wrap: anywhere; }
        .docs-content .opblock-summary-path { word-break: break-word; }
    </style>
</head>
<body>
    <header class="border-b border-border bg-surface">
        <div class="shell-container flex min-h-16 items-center"><x-brand /></div>
    </header>
    <main class="shell-container docs-content py-6 sm:py-8">
        <p class="eyebrow">API documentation</p>
        <p class="mt-3 max-w-prose text-sm leading-6 text-text-muted">
            Register or sign in, then paste the returned token into <strong>Authorize</strong>. Amounts use whole rupiah. Try requests with a test account because submissions save to the configured database.
        </p>
        <a href="{{ url('/openapi.json') }}" class="text-link mt-1 text-sm">OpenAPI JSON<x-icon name="arrow-right" class="size-4" /></a>
        <div id="swagger-ui">
            <h1 class="page-title">ReWortha API</h1>
            <p class="mt-2 text-sm text-text-muted" role="status">Loading API endpoints…</p>
        </div>
    </main>
    <footer class="shell-container">
        <div class="border-t border-border py-3 text-sm text-text-muted">ReWortha</div>
    </footer>
    <script src="{{ asset('vendor/swagger-ui/swagger-ui-bundle.js') }}"></script>
    <script>
        SwaggerUIBundle({
            url: @json(url('/openapi.json')),
            dom_id: '#swagger-ui',
            deepLinking: true,
            persistAuthorization: false,
            validatorUrl: null,
            supportedSubmitMethods: ['get', 'post', 'patch', 'delete'],
            presets: [SwaggerUIBundle.presets.apis],
        });
    </script>
</body>
</html>
