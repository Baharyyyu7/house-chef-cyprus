<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
        $exceptions->render(function (Throwable $exception, Request $request) {
            if ($request->is('api/*') && $exception instanceof ValidationException) {
                return response()->json(['message' => 'İstek tamamlanamadı.', 'error_code' => 'VALIDATION_ERROR', 'errors' => $exception->errors(), 'request_id' => (string) Str::ulid(), 'status' => 422], 422);
            }
            if ($request->is('api/*') && $exception instanceof NotFoundHttpException) {
                return response()->json(['message' => 'İstek tamamlanamadı.', 'error_code' => 'RESOURCE_NOT_FOUND', 'errors' => [], 'request_id' => (string) Str::ulid(), 'status' => 404], 404);
            }
            if ($request->is('api/*')) {
                return response()->json(['message' => 'İstek tamamlanamadı.', 'error_code' => 'INTERNAL_ERROR', 'errors' => [], 'request_id' => (string) Str::ulid(), 'status' => 500], 500);
            }
        });
    })->create();
