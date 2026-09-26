<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PayPalService
{
    /**
     * @return array{client_id: string, secret: string, mode: string}
     */
    private function credentials(?array $override = null): array
    {
        if ($override !== null) {
            $credentials = $override;
        } else {
            $setting = SystemSetting::query()->first();
            $useAdminSettings = filled($setting?->paypal_client_id) && filled($setting?->paypal_secret);
            $credentials = $useAdminSettings ? [
                'client_id' => $setting->paypal_client_id,
                'secret' => $setting->paypal_secret,
                'mode' => $setting->paypal_mode,
            ] : [
                'client_id' => config('services.paypal.client_id'),
                'secret' => config('services.paypal.secret'),
                'mode' => config('services.paypal.mode', 'sandbox'),
            ];
        }

        $clientId = trim((string) ($credentials['client_id'] ?? ''));
        $secret = trim((string) ($credentials['secret'] ?? ''));
        $mode = ($credentials['mode'] ?? 'sandbox') === 'live' ? 'live' : 'sandbox';

        if ($clientId === '' || $secret === '') {
            throw new RuntimeException('PayPal credentials are not configured.');
        }

        return ['client_id' => $clientId, 'secret' => $secret, 'mode' => $mode];
    }

    private function baseUrl(string $mode): string
    {
        return $mode === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    private function client(?array $override = null): PendingRequest
    {
        $credentials = $this->credentials($override);

        $tokenResponse = Http::asForm()
            ->acceptJson()
            ->connectTimeout(5)
            ->timeout(15)
            ->withBasicAuth($credentials['client_id'], $credentials['secret'])
            ->post($this->baseUrl($credentials['mode']).'/v1/oauth2/token', ['grant_type' => 'client_credentials'])
            ->throw();

        $accessToken = $tokenResponse->json('access_token');
        if (! is_string($accessToken) || $accessToken === '') {
            throw new RuntimeException('PayPal did not return an access token.');
        }

        return Http::withToken($accessToken)
            ->acceptJson()
            ->connectTimeout(5)
            ->timeout(20)
            ->baseUrl($this->baseUrl($credentials['mode']));
    }

    public function clientId(): string
    {
        return $this->credentials()['client_id'];
    }

    public function verifyCredentials(?array $override = null): array
    {
        $credentials = $this->credentials($override);
        $this->client($credentials);

        return ['authenticated' => true, 'mode' => $credentials['mode']];
    }

    public function createOrder(string $invoiceNumber, string $amount, string $currency = 'GBP'): array
    {
        return $this->client()
            ->withHeaders(['PayPal-Request-Id' => 'invoice-'.hash('sha256', $invoiceNumber)])
            ->post('/v2/checkout/orders', [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'reference_id' => $invoiceNumber,
                    'invoice_id' => $invoiceNumber,
                    'description' => 'InstaDrop courier service',
                    'amount' => ['currency_code' => $currency, 'value' => $amount],
                ]],
                'payment_source' => [
                    'paypal' => [
                        'experience_context' => [
                            'shipping_preference' => 'NO_SHIPPING',
                            'user_action' => 'PAY_NOW',
                        ],
                    ],
                ],
            ])->throw()->json();
    }

    public function captureOrder(string $orderId): array
    {
        return $this->client()
            ->withHeaders(['PayPal-Request-Id' => 'capture-'.$orderId])
            ->withBody('{}', 'application/json')
            ->send('POST', '/v2/checkout/orders/'.rawurlencode($orderId).'/capture')
            ->throw()->json();
    }

    public function getOrder(string $orderId): array
    {
        return $this->client()
            ->get('/v2/checkout/orders/'.rawurlencode($orderId))
            ->throw()->json();
    }
}
