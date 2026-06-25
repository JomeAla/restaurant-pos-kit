<?php

namespace App\Services;

use App\Models\PaymentGateway;
use App\Models\PaymentGatewayLog;
use Illuminate\Support\Facades\Http;

class PaystackService
{
    protected ?PaymentGateway $gateway;
    protected string $baseUrl;

    public function __construct()
    {
        $this->gateway = PaymentGateway::where('gateway', 'paystack')->where('is_active', true)->first();
        $this->baseUrl = 'https://api.paystack.co';
    }

    public function getSecretKey(): ?string
    {
        if (!$this->gateway || !$this->gateway->credentials) return null;
        $creds = $this->gateway->decrypted_credentials;
        return $creds['secret_key'] ?? null;
    }

    public function getPublicKey(): ?string
    {
        if (!$this->gateway || !$this->gateway->credentials) return null;
        $creds = $this->gateway->decrypted_credentials;
        return $creds['public_key'] ?? null;
    }

    public function initializeTransaction(float $amount, string $email, string $reference, array $metadata = []): array
    {
        $secret = $this->getSecretKey();
        if (!$secret) return ['status' => false, 'message' => 'Gateway not configured'];

        $response = Http::withToken($secret)->post($this->baseUrl . '/transaction/initialize', [
            'amount' => (int)($amount * 100),
            'email' => $email,
            'reference' => $reference,
            'metadata' => $metadata,
        ]);

        $this->logTransaction('initialize', ['amount' => $amount, 'email' => $email, 'reference' => $reference], $response->json(), $response->successful() ? 'success' : 'failed', $reference, $amount);

        return $response->json();
    }

    public function verifyTransaction(string $reference): array
    {
        $secret = $this->getSecretKey();
        if (!$secret) return ['status' => false, 'message' => 'Gateway not configured'];

        $response = Http::withToken($secret)->get($this->baseUrl . "/transaction/verify/{$reference}");

        $this->logTransaction('verify', ['reference' => $reference], $response->json(), $response->successful() ? 'success' : 'failed', $reference);

        return $response->json();
    }

    public function refundTransaction(string $reference, float $amount = 0): array
    {
        $secret = $this->getSecretKey();
        if (!$secret) return ['status' => false, 'message' => 'Gateway not configured'];

        $response = Http::withToken($secret)->post($this->baseUrl . '/transaction/refund', [
            'reference' => $reference,
        ]);

        $this->logTransaction('refund', ['reference' => $reference, 'amount' => $amount], $response->json(), $response->successful() ? 'success' : 'failed', $reference, $amount);

        return $response->json();
    }

    public function handleWebhook(array $payload): array
    {
        $event = $payload['event'] ?? '';
        $data = $payload['data'] ?? [];

        switch ($event) {
            case 'charge.success':
                return ['event' => 'payment.success', 'reference' => $data['reference'] ?? '', 'amount' => ($data['amount'] ?? 0) / 100, 'status' => 'success'];
            case 'charge.failed':
                return ['event' => 'payment.failed', 'reference' => $data['reference'] ?? '', 'status' => 'failed'];
            default:
                return ['event' => $event, 'status' => 'unhandled'];
        }
    }

    protected function logTransaction(string $action, array $requestData, ?array $responseData, string $status, ?string $reference = null, ?float $amount = null): void
    {
        PaymentGatewayLog::create([
            'payment_gateway_id' => $this->gateway?->id,
            'gateway' => 'paystack',
            'request_payload' => $requestData,
            'response_payload' => $responseData,
            'status' => $status,
            'reference' => $reference,
            'amount' => $amount,
        ]);
    }
}
