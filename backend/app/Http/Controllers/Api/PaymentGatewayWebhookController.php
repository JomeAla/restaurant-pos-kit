<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use App\Models\Payment;
use App\Services\PaystackService;
use App\Services\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentGatewayWebhookController extends Controller
{
    public function handlePaystack(Request $request): JsonResponse
    {
        $payload = $request->all();
        $signature = $request->header('x-paystack-signature');

        $gateway = PaymentGateway::where('gateway', 'paystack')->first();
        if ($gateway?->webhook_secret) {
            $expected = hash_hmac('sha512', $request->getContent(), $gateway->webhook_secret);
            if (!hash_equals($expected, $signature)) {
                return response()->json(['message' => 'Invalid signature'], 401);
            }
        }

        $service = new PaystackService();
        $result = $service->handleWebhook($payload);

        if (in_array($result['event'], ['payment.success', 'payment.failed'])) {
            $status = $result['event'] === 'payment.success' ? 'completed' : 'failed';
            Payment::where('reference', $result['reference'])->update(['status' => $status]);
        }

        return response()->json(['message' => 'Webhook handled']);
    }

    public function handleStripe(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('stripe-signature');

        $gateway = PaymentGateway::where('gateway', 'stripe')->first();
        $webhookSecret = $gateway?->webhook_secret ?? '';

        $service = new StripeService();
        $result = $service->handleWebhook($payload, $sigHeader, $webhookSecret);

        if (in_array($result['event'] ?? '', ['payment.success', 'payment.failed'])) {
            $status = $result['event'] === 'payment.success' ? 'completed' : 'failed';
            Payment::where('reference', $result['reference'])->update(['status' => $status]);
        }

        return response()->json(['message' => 'Webhook handled']);
    }
}
