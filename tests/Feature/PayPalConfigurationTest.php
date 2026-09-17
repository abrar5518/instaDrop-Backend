<?php

namespace Tests\Feature;

use App\Models\SystemSetting;
use App\Services\PayPalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PayPalConfigurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_uses_encrypted_admin_live_credentials_for_orders(): void
    {
        SystemSetting::query()->create([
            'paypal_client_id' => 'live-client-id',
            'paypal_secret' => 'live-client-secret',
            'paypal_mode' => 'live',
        ]);

        $storedSecret = DB::table('system_settings')->value('paypal_secret');
        $this->assertNotSame('live-client-secret', $storedSecret);
        $this->assertSame('live-client-secret', SystemSetting::query()->firstOrFail()->paypal_secret);

        Http::fake([
            'https://api-m.paypal.com/v1/oauth2/token' => Http::response(['access_token' => 'access-token']),
            'https://api-m.paypal.com/v2/checkout/orders' => Http::response(['id' => 'PAYPALORDER01'], 201),
        ]);

        $order = app(PayPalService::class)->createOrder('INV-1001', '120.00');

        $this->assertSame('PAYPALORDER01', $order['id']);
        Http::assertSent(fn (Request $request) => $request->url() === 'https://api-m.paypal.com/v1/oauth2/token'
            && $request->hasHeader('Authorization', 'Basic '.base64_encode('live-client-id:live-client-secret')));
        Http::assertSent(fn (Request $request) => $request->url() === 'https://api-m.paypal.com/v2/checkout/orders'
            && $request->hasHeader('PayPal-Request-Id')
            && $request['purchase_units'][0]['invoice_id'] === 'INV-1001'
            && $request['purchase_units'][0]['amount'] === ['currency_code' => 'GBP', 'value' => '120.00']
            && $request['payment_source']['paypal']['experience_context']['shipping_preference'] === 'NO_SHIPPING');
    }

    public function test_service_falls_back_to_environment_credentials_when_admin_credentials_are_incomplete(): void
    {
        config()->set('services.paypal.client_id', 'environment-client-id');
        config()->set('services.paypal.secret', 'environment-secret');
        config()->set('services.paypal.mode', 'sandbox');

        SystemSetting::query()->create([
            'paypal_client_id' => 'incomplete-admin-client-id',
            'paypal_secret' => null,
            'paypal_mode' => 'live',
        ]);

        Http::fake([
            'https://api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response(['access_token' => 'access-token']),
        ]);

        $result = app(PayPalService::class)->verifyCredentials();

        $this->assertTrue($result['authenticated']);
        $this->assertSame('sandbox', $result['mode']);
        Http::assertSent(fn (Request $request) => $request->url() === 'https://api-m.sandbox.paypal.com/v1/oauth2/token'
            && $request->hasHeader('Authorization', 'Basic '.base64_encode('environment-client-id:environment-secret')));
    }
}
