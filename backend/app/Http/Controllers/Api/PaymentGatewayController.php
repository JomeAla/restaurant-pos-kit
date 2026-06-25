<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use App\Models\PaymentGatewayLog;
use App\Services\PaystackService;
use App\Services\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentGatewayController extends Controller
{
    public function index(): JsonResponse
    {
        $gateways = PaymentGateway::select('id', 'gateway', 'label', 'is_sandbox', 'is_active', 'webhook_url', 'created_at', 'updated_at')->get();
        return response()->json($gateways);
    }

    public function show(PaymentGateway $gateway): JsonResponse
    {
        return response()->json($gateway->makeHidden('credentials'));
    }

    public function update(Request $request, PaymentGateway $gateway): JsonResponse
    {
        $validated = $request->validate([
            'gateway' => 'sometimes|string|max:50',
            'label' => 'sometimes|string|max:255',
            'credentials' => 'sometimes|array',
            'is_sandbox' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
        ]);

        if (isset($validated['credentials'])) {
            $gateway->decrypted_credentials = $validated['credentials'];
            unset($validated['credentials']);
        }

        $gateway->update($validated);

        return response()->json($gateway->fresh()->makeHidden('credentials'));
    }

    public function test(Request $request, PaymentGateway $gateway): JsonResponse
    {
        $gatewayName = $gateway->gateway;

        if ($gatewayName === 'paystack') {
            $service = new PaystackService();
            $result = $service->verifyTransaction('test_ref_' . time());
            $success = isset($result['status']) && $result['status'] !== false;
            return response()->json(['success' => $success, 'message' => $success ? 'Connection successful' : 'Connection failed', 'response' => $result]);
        }

        if ($gatewayName === 'stripe') {
            $service = new StripeService();
            $result = $service->createPaymentIntent(1.00, 'usd', ['test' => true]);
            $success = isset($result['status']) && $result['status'] === true;
            return response()->json(['success' => $success, 'message' => $success ? 'Connection successful' : 'Connection failed', 'response' => $result]);
        }

        return response()->json(['success' => false, 'message' => 'Unknown gateway'], 400);
    }

    public function stripeConfig(): JsonResponse
    {
        $gateway = PaymentGateway::where('gateway', 'stripe')->where('is_active', true)->first();
        if (!$gateway || !$gateway->credentials) {
            return response()->json(['configured' => false]);
        }
        $creds = $gateway->decrypted_credentials;
        return response()->json([
            'configured' => true,
            'publishable_key' => $creds['publishable_key'] ?? null,
        ]);
    }

    public function logs(Request $request, PaymentGateway $gateway): JsonResponse
    {
        $logs = PaymentGatewayLog::where('payment_gateway_id', $gateway->id)
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json($logs);
    }
}
