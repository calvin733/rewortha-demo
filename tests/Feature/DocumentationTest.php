<?php

use Illuminate\Support\Facades\Route;

it('serves the interactive docs and versioned OpenAPI contract', function (): void {
    $this->get('/')->assertRedirect('/login');
    $this->get('/docs')->assertOk()->assertSee('SwaggerUIBundle')->assertSee('ReWortha');
    $this->get('/openapi.json')->assertOk()->assertHeader('Content-Type', 'application/json');
});

it('documents every API operation with matching methods and paths', function (): void {
    $spec = json_decode(file_get_contents(base_path('docs/openapi.json')), true, flags: JSON_THROW_ON_ERROR);
    $actual = [];
    foreach (Route::getRoutes() as $route) {
        if (str_starts_with($route->uri(), 'api/v1/')) {
            foreach (array_diff($route->methods(), ['HEAD']) as $method) {
                $actual[] = strtolower($method).' /'.substr($route->uri(), strlen('api/v1/'));
            }
        }
    }
    $documented = [];
    foreach ($spec['paths'] as $path => $operations) {
        foreach (array_keys($operations) as $method) {
            $documented[] = $method.' '.$path;
        }
    }
    sort($actual);
    sort($documented);
    expect($documented)->toBe($actual);
});
