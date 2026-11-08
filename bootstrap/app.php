<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('api/*') ? null : '/login');
        $middleware->redirectUsersTo(fn (Request $request) => '/dashboard');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
        $exceptions->respond(function (Response $response, Throwable $exception, Request $request) {
            if (! $request->is('api/*') || $response->getStatusCode() < 400) {
                return $response;
            }
            $status = $response->getStatusCode();
            $message = match ($status) {
                401 => 'Unauthenticated.', 403 => 'This action is not allowed.',
                404 => 'Resource not found.', 405 => 'Method not allowed.',
                409 => $exception->getMessage(), 422 => 'The given data was invalid.',
                429 => 'Too many requests.',
                default => $status >= 500 ? 'An unexpected error occurred.' : 'The request could not be processed.',
            };
            $body = ['message' => $message];
            if ($exception instanceof ValidationException) {
                $body['errors'] = $exception->errors();
            }
            $headers = $response->headers->all();
            unset($headers['content-type'], $headers['content-length']);

            return response()->json($body, $status, $headers);
        });
    })->create();
