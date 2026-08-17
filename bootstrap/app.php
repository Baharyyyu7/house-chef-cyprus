<?php

use App\Contracts\Observability\ErrorReporterInterface;
use App\Http\Middleware\RequestContext;
use App\Http\Middleware\SecureHeaders;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
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
        // These preferences are intentionally written by browser-side JavaScript so a
        // tag is loaded only after a visitor has opted in. They contain no account or
        // session data and must therefore remain readable by Laravel.
        //
        // hc_advertising_consent belongs here for the same reason as the analytics one:
        // left encrypted, Laravel could never read what the browser wrote, the ad slot
        // saw a null consent on every request, and no advertising could ever render —
        // silently, with the configuration looking correct.
        $middleware->encryptCookies(except: ['hc_analytics_consent', 'hc_advertising_consent']);
        $middleware->append(RequestContext::class);
        $middleware->append(SecureHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
        $exceptions->render(function (Throwable $exception, Request $request) {
            if ($request->is('api/*') && $exception instanceof AuthenticationException) {
                return response()->json(['error' => ['code' => 'UNAUTHENTICATED', 'message' => 'Kimlik doğrulaması gerekli.', 'details' => []], 'request_id' => (string) $request->attributes->get('request_id')], 401)->header('X-Request-ID', (string) $request->attributes->get('request_id'));
            }
            if ($request->is('api/*') && $exception instanceof ValidationException) {
                return response()->json(['error' => ['code' => 'VALIDATION_ERROR', 'message' => 'İstek tamamlanamadı.', 'details' => $exception->errors()], 'request_id' => (string) $request->attributes->get('request_id')], 422)->header('X-Request-ID', (string) $request->attributes->get('request_id'));
            }
            if ($request->is('api/*') && $exception instanceof NotFoundHttpException) {
                return response()->json(['error' => ['code' => 'RESOURCE_NOT_FOUND', 'message' => 'İstek tamamlanamadı.', 'details' => []], 'request_id' => (string) $request->attributes->get('request_id')], 404)->header('X-Request-ID', (string) $request->attributes->get('request_id'));
            }
            if ($request->is('api/*') && $exception instanceof MethodNotAllowedHttpException) {
                return response()->json(['error' => ['code' => 'METHOD_NOT_ALLOWED', 'message' => 'İstek tamamlanamadı.', 'details' => []], 'request_id' => (string) $request->attributes->get('request_id')], 405)->header('X-Request-ID', (string) $request->attributes->get('request_id'));
            }
            if ($request->is('api/*') && $exception instanceof BadRequestHttpException) {
                return response()->json(['error' => ['code' => 'MALFORMED_REQUEST', 'message' => 'İstek tamamlanamadı.', 'details' => []], 'request_id' => (string) $request->attributes->get('request_id')], 400)->header('X-Request-ID', (string) $request->attributes->get('request_id'));
            }
            if ($request->is('api/*') && $exception instanceof HttpExceptionInterface) {
                $status = $exception->getStatusCode();
                $code = match ($status) {
                    413 => 'PAYLOAD_TOO_LARGE',
                    415 => 'UNSUPPORTED_MEDIA_TYPE',
                    429 => 'RATE_LIMIT_EXCEEDED',
                    default => 'REQUEST_ERROR',
                };

                return response()->json(['error' => ['code' => $code, 'message' => 'İstek tamamlanamadı.', 'details' => []], 'request_id' => (string) $request->attributes->get('request_id')], $status, $exception->getHeaders())->header('X-Request-ID', (string) $request->attributes->get('request_id'));
            }
            if ($request->is('api/*')) {
                app(ErrorReporterInterface::class)->report($exception, ['request_id' => $request->attributes->get('request_id')]);

                return response()->json(['error' => ['code' => 'INTERNAL_ERROR', 'message' => 'İstek tamamlanamadı.', 'details' => []], 'request_id' => (string) $request->attributes->get('request_id')], 500)->header('X-Request-ID', (string) $request->attributes->get('request_id'));
            }
        });
    })->create();
