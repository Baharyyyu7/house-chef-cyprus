<?php

use App\Contracts\Observability\ErrorReporterInterface;
use App\Http\Middleware\RequestContext;
use App\Http\Middleware\SecureHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        __DIR__.'/../app/Console/Commands',
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(RequestContext::class);
        $middleware->append(SecureHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
        $exceptions->render(function (Throwable $exception, Request $request) {
            if ($request->is('api/*') && $exception instanceof ValidationException) {
                return response()->json(['message' => 'İstek tamamlanamadı.', 'error_code' => 'VALIDATION_ERROR', 'errors' => $exception->errors(), 'request_id' => (string) $request->attributes->get('request_id'), 'status' => 422], 422);
            }
            if ($request->is('api/*') && $exception instanceof NotFoundHttpException) {
                return response()->json(['message' => 'İstek tamamlanamadı.', 'error_code' => 'RESOURCE_NOT_FOUND', 'errors' => [], 'request_id' => (string) $request->attributes->get('request_id'), 'status' => 404], 404);
            }
            if ($request->is('api/*')) {
                app(ErrorReporterInterface::class)->report($exception, ['request_id' => $request->attributes->get('request_id')]);

                return response()->json(['message' => 'İstek tamamlanamadı.', 'error_code' => 'INTERNAL_ERROR', 'errors' => [], 'request_id' => (string) $request->attributes->get('request_id'), 'status' => 500], 500);
            }
        });
    })->create();
