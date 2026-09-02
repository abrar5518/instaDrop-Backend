<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PayPalService
{
    private function baseUrl(): string
    {
        return config('services.paypal.mode') === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    private function client(): PendingRequest
    {
        $clientId = config('services.paypal.client_id');
        $secret = config('services.paypal.secret');
        if (!$clientId || !$secret) {
            throw new RuntimeException('PayPal credentials are not configured.');
        }

        $tokenResponse = Http::asForm()->withBasicAuth($clientId, $secret)
            ->post($this->baseUrl() . '/v1/oauth2/token', ['grant_type' => 'client_credentials'])
            ->throw();

        return Http::withToken($tokenResponse->json('access_token'))->acceptJson();
    }

    public function verifyCredentials(): array
    {
        $this->client();
        return ['authenticated' => true, 'mode' => config('services.paypal.mode')];
    }

    public function createOrder(string $invoiceNumber, string $amount, string $currency = 'GBP'): array
    {
        return $this->client()->post($this->baseUrl() . '/v2/checkout/orders', [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'reference_id' => $invoiceNumber,
                'invoice_id' => $invoiceNumber,
                'amount' => ['currency_code' => $currency, 'value' => $amount],
            ]],
        ])->throw()->json();
    }

    public function captureOrder(string $orderId): array
    {
        return $this->client()->withHeaders(['PayPal-Request-Id' => 'capture-' . $orderId])
            ->post($this->baseUrl() . '/v2/checkout/orders/' . rawurlencode($orderId) . '/capture')
            ->throw()->json();
    }
}
