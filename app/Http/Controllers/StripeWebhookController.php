<?php

namespace App\Http\Controllers;

use App\Services\Payments\StripeWebhookProcessor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use UnexpectedValueException;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request, StripeWebhookProcessor $processor): JsonResponse
    {
        $secret = (string) config('services.stripe.webhook_secret', '');

        if ($secret === '') {
            return response()->json(['message' => 'Webhook yapılandırılmadı.'], 503);
        }

        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                (string) $request->header('Stripe-Signature'),
                $secret,
            );
        } catch (UnexpectedValueException|SignatureVerificationException) {
            return response()->json(['message' => 'Geçersiz webhook imzası.'], 400);
        }

        $payload = $event->toArray();
        $expectedLiveMode = (bool) config('services.stripe.live_mode', false);

        if (! array_key_exists('livemode', $payload) || (bool) $payload['livemode'] !== $expectedLiveMode) {
            return response()->json(['message' => 'Webhook çalışma modu eşleşmiyor.'], 400);
        }

        $processor->handle($payload);

        return response()->json(['received' => true]);
    }
}
