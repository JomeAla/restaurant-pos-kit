<?php

namespace App\Services;

use App\Models\PaymentGateway;
use App\Models\PaymentGatewayLog;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Refund;

class StripeService
{
    protected ?PaymentGateway $gateway;

    public function __construct()
    {
        $this->gateway = PaymentGateway::where('gateway', 'stripe')->where('is_active', true)->first();
        if ($this->gateway && $this->gateway->credentials) {
            $creds = $this->gateway->decrypted_credentials;
            Stripe::setApiKey($creds['secret_key'] ?? '');
        }
    }

    public function getPublishableKey(): ?string
    {
        if (!$this->gateway || !$this->gateway->credentials) return null;
        $creds = $this->gateway->decrypted_credentials;
        return $creds['publishable_key'] ?? null;
    }

    public function createPaymentIntent(float $amount, string $currency = 'usd', array $metadata = []): array
    {
        try {
            $intent = PaymentIntent::create([
                'amount' => (int)($amount * 100),
                'currency' => $currency,
                'metadata' => $metadata,
                'automatic_payment_methods' => ['enabled' => true],
            ]);

            $this->logTransaction('create_payment_intent', ['amount' => $amount, 'currency' => $currency], ['id' => $intent->id, 'client_secret' => $intent->client_secret], 'success', $intent->id, $amount);

            return ['status' => true, 'client_secret' => $intent->client_secret, 'id' => $intent->id];
        } catch (\Exception $e) {
            $this->logTransaction('create_payment_intent', ['amount' => $amount, 'currency' => $currency], ['error' => $e->getMessage()], 'failed', null, $amount);
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function confirmPaymentIntent(string $paymentIntentId): array
    {
        try {
            $intent = PaymentIntent::retrieve($paymentIntentId);
            return ['status' => true, 'intent' => $intent];
        } catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function refundPayment(string $paymentIntentId, float $amount = 0): array
    {
        try {
            $params = ['payment_intent' => $paymentIntentId];
            if ($amount > 0) $params['amount'] = (int)($amount * 100);

            $refund = Refund::create($params);

            $this->logTransaction('refund', ['payment_intent' => $paymentIntentId, 'amount' => $amount], ['id' => $refund->id, 'status' => $refund->status], 'success', $refund->id, $amount);

            return ['status' => true, 'refund' => $refund];
        } catch (\Exception $e) {
            $this->logTransaction('refund', ['payment_intent' => $paymentIntentId, 'amount' => $amount], ['error' => $e->getMessage()], 'failed', null, $amount);
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function handleWebhook(string $payload, string $sigHeader, string $webhookSecret): array
    {
        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
            $intent = $event->data->object;

            switch ($event->type) {
                case 'payment_intent.succeeded':
                    return ['event' => 'payment.success', 'reference' => $intent->id, 'status' => 'success'];
                case 'payment_intent.payment_failed':
                    return ['event' => 'payment.failed', 'reference' => $intent->id, 'status' => 'failed'];
                default:
                    return ['event' => $event->type, 'status' => 'unhandled'];
            }
        } catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    protected function logTransaction(string $action, array $requestData, ?array $responseData, string $status, ?string $reference = null, ?float $amount = null): void
    {
        PaymentGatewayLog::create([
            'payment_gateway_id' => $this->gateway?->id,
            'gateway' => 'stripe',
            'request_payload' => $requestData,
            'response_payload' => $responseData,
            'status' => $status,
            'reference' => $reference,
            'amount' => $amount,
        ]);
    }
}
