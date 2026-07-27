<?php

declare(strict_types=1);

namespace App\Services\Observability;

use App\Contracts\Observability\ErrorReporterInterface;
use App\Models\SystemEvent;
use Illuminate\Support\Facades\Log;
use Throwable;

final class LocalLogErrorReporter implements ErrorReporterInterface
{
    public function report(Throwable $exception, array $context = []): void
    {
        $context = array_filter($context, fn (mixed $value, string $key): bool => ! in_array(strtolower($key), ['password', 'token', 'authorization', 'cookie', 'session'], true), ARRAY_FILTER_USE_BOTH);
        Log::error('application.error', $context + ['error_code' => 'INTERNAL_ERROR', 'exception_class' => class_basename($exception)]);
        SystemEvent::create(['event_type' => 'application_error', 'severity' => 'error', 'message' => 'Uygulama hatası güvenli olarak kaydedildi.', 'correlation_id' => $context['request_id'] ?? null, 'metadata' => ['error_code' => 'INTERNAL_ERROR', 'exception' => class_basename($exception)]]);
    }
}
