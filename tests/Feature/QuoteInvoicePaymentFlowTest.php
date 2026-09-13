<?php

namespace Tests\Feature;

use App\Jobs\SendPaymentNotifications;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\QuoteRequest;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\EmailNotificationService;
use App\Services\PayPalService;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Bus;
use Mockery;
use Tests\TestCase;

class QuoteInvoicePaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([ValidateCsrfToken::class, ThrottleRequests::class]);
    }

    private function quote(): QuoteRequest
    {
        return QuoteRequest::create([
            'quote_number' => 'Q-TEST01',
            'first_name' => 'Payment',
            'last_name' => 'Customer',
            'email' => 'customer@example.com',
            'phone' => '+447700900123',
            'contact_preference' => 'email',
            'collection_postcode' => 'M1 1AE',
            'delivery_postcode' => 'SW1A 1AA',
            'vehicle_type' => 'small_van',
            'timescale' => 'same_day',
            'status' => 'pending',
        ]);
    }

    private function paidInvoice(): Invoice
    {
        $quote = $this->quote();
        $order = Order::create([
            'quote_request_id' => $quote->id,
            'tracking_number' => 'INSTA-TEST01',
            'customer_name' => $quote->full_name,
            'customer_email' => $quote->email,
            'customer_phone' => $quote->phone,
            'preferred_contact_method' => 'email',
            'pickup_address' => 'Pickup address',
            'delivery_address' => 'Delivery address',
            'vehicle_type' => 'small_van',
            'carrier_name' => 'InstaDrop Fleet',
            'quoted_selling_price' => 180,
            'status' => 'paid',
        ]);

        return Invoice::create([
            'order_id' => $order->id,
            'invoice_number' => 'INV-TEST01',
            'payment_token' => 'PAY-TEST01',
            'subtotal' => 150,
            'vat_amount' => 30,
            'total_amount' => 180,
            'status' => 'paid',
            'payment_method' => 'paypal',
            'payment_transaction_id' => 'PAYPAL-TEST01',
            'paid_at' => now(),
        ]);
    }

    public function test_quote_page_matches_the_simple_payment_link_form(): void
    {
        $quote = $this->quote();

        $this->actingAs(User::factory()->create())
            ->get('/admin/quotes/' . $quote->id)
            ->assertOk()
            ->assertSee('Full Pickup Address')
            ->assertSee('Full Delivery Address')
            ->assertSee('Quoted Selling Price')
            ->assertSee('Send Payment Link to Payment')
            ->assertDontSee('Driver / Rider Payout Cost')
            ->assertDontSee('Suggested Rate Calculator')
            ->assertDontSee('Send Quote Instant via WhatsApp')
            ->assertDontSee('Copy Checkout Link');
    }

    public function test_admin_form_creates_invoice_and_sends_customer_and_dispatch_emails(): void
    {
        SystemSetting::query()->firstOrCreate()->update([
            'vat_rate' => 20,
            'admin_notification_email' => 'dispatch@instadrop.uk',
        ]);
        $quote = $this->quote();

        $email = Mockery::mock(EmailNotificationService::class);
        $email->shouldReceive('sendInvoicePaymentEmail')->once()->with(Mockery::type(Invoice::class))->andReturnTrue();
        $email->shouldReceive('sendAdminInvoiceCreatedEmail')->once()->with(Mockery::type(Invoice::class))->andReturnTrue();
        $this->app->instance(EmailNotificationService::class, $email);

        $whatsApp = Mockery::mock(WhatsAppService::class);
        $whatsApp->shouldReceive('sendQuotationAndPaymentLink')->once()->with(Mockery::type(Invoice::class))->andReturnTrue();
        $this->app->instance(WhatsAppService::class, $whatsApp);

        $this->actingAs(User::factory()->create())
            ->post('/admin/quotes/' . $quote->id . '/invoice', [
                'pickup_address' => 'Unit 4 Logistics Park, M1 1AE',
                'delivery_address' => 'Building 12 Commerce Center, SW1A 1AA',
                'quoted_selling_price' => '150.00',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success')
            ->assertRedirect();

        $invoice = Invoice::firstOrFail();
        $this->assertEquals(150.00, $invoice->subtotal);
        $this->assertEquals(30.00, $invoice->vat_amount);
        $this->assertEquals(180.00, $invoice->total_amount);
        $this->assertSame('pending_payment', $invoice->order->status);
        $this->assertSame('quoted', $quote->fresh()->status);
    }

    public function test_verified_payment_dispatches_customer_and_admin_notifications_synchronously(): void
    {
        $invoice = $this->paidInvoice();

        $email = Mockery::mock(EmailNotificationService::class);
        $email->shouldReceive('sendPaymentConfirmationEmail')->once()->with(Mockery::on(fn ($value) => $value->is($invoice)))->andReturnTrue();
        $email->shouldReceive('sendAdminPaymentReceivedEmail')->once()->with(Mockery::on(fn ($value) => $value->is($invoice)))->andReturnTrue();

        $whatsApp = Mockery::mock(WhatsAppService::class);
        $whatsApp->shouldReceive('sendPaymentConfirmation')->once()->with(Mockery::on(fn ($value) => $value->is($invoice)))->andReturnTrue();
        $whatsApp->shouldReceive('sendAdminPaymentReceivedAlert')->once()->with(Mockery::on(fn ($value) => $value->is($invoice)))->andReturnTrue();

        (new SendPaymentNotifications($invoice->id))->handle($email, $whatsApp);
    }

    public function test_paypal_capture_marks_invoice_paid_and_dispatches_notifications_synchronously(): void
    {
        $invoice = $this->paidInvoice();
        $invoice->update(['status' => 'unpaid', 'payment_method' => null, 'payment_transaction_id' => null, 'paid_at' => null]);
        $invoice->order->update(['status' => 'pending_payment']);

        $payPal = Mockery::mock(PayPalService::class);
        $payPal->shouldReceive('captureOrder')->once()->with('PAYPAL-ORDER-01')->andReturn([
            'status' => 'COMPLETED',
            'purchase_units' => [[
                'reference_id' => $invoice->invoice_number,
                'invoice_id' => $invoice->invoice_number,
                'payments' => ['captures' => [[
                    'id' => 'PAYPAL-CAPTURE-01',
                    'status' => 'COMPLETED',
                    'amount' => ['currency_code' => 'GBP', 'value' => '180.00'],
                ]]],
            ]],
        ]);
        $this->app->instance(PayPalService::class, $payPal);
        Bus::fake([SendPaymentNotifications::class]);

        $this->postJson('/api/v1/payments/paypal/capture', [
            'payment_token' => $invoice->payment_token,
            'paypal_order_id' => 'PAYPAL-ORDER-01',
        ])->assertOk()->assertJsonPath('success', true);

        $this->assertSame('paid', $invoice->fresh()->status);
        $this->assertSame('paid', $invoice->order->fresh()->status);
        Bus::assertDispatchedSync(SendPaymentNotifications::class, fn ($job) => $job->invoiceId === $invoice->id);
    }
}
