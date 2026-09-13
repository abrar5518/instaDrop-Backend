<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\QuoteRequest;
use App\Models\SystemSetting;
use App\Services\EmailNotificationService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    public function __construct(
        private WhatsAppService $whatsAppService,
        private EmailNotificationService $emailService,
    ) {
    }

    /** Admin action to create an invoice and send its secure payment link. */
    public function generate(Request $request, QuoteRequest $quote)
    {
        $validated = $request->validate([
            'quoted_selling_price' => ['required', 'numeric', 'min:1'],
            'pickup_address' => ['required', 'string', 'max:1000'],
            'delivery_address' => ['required', 'string', 'max:1000'],
        ]);

        $vatRate = (float) (SystemSetting::query()->value('vat_rate') ?? 20) / 100;
        $subtotal = round((float) $validated['quoted_selling_price'], 2);
        $vatAmount = round($subtotal * $vatRate, 2);
        $totalAmount = round($subtotal + $vatAmount, 2);

        $invoice = DB::transaction(function () use ($quote, $validated, $subtotal, $vatAmount, $totalAmount) {
            $order = Order::create([
                'quote_request_id' => $quote->id,
                'tracking_number' => 'INSTA-' . strtoupper(Str::random(8)),
                'customer_name' => $quote->full_name,
                'customer_email' => $quote->email,
                'customer_phone' => $quote->phone,
                'preferred_contact_method' => $quote->contact_preference,
                'pickup_address' => $validated['pickup_address'],
                'delivery_address' => $validated['delivery_address'],
                'vehicle_type' => $quote->vehicle_type,
                'carrier_name' => 'InstaDrop Fleet',
                'quoted_selling_price' => $totalAmount,
                'status' => 'pending_payment',
            ]);

            $invoice = Invoice::create([
                'order_id' => $order->id,
                'invoice_number' => 'INV-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6)),
                'payment_token' => 'PAY-' . Str::uuid(),
                'subtotal' => $subtotal,
                'vat_amount' => $vatAmount,
                'total_amount' => $totalAmount,
                'status' => 'unpaid',
            ]);

            $quote->update(['status' => 'quoted']);

            return $invoice->load('order.quoteRequest');
        });

        $customerEmailSent = $this->emailService->sendInvoicePaymentEmail($invoice);
        $adminEmailSent = $this->emailService->sendAdminInvoiceCreatedEmail($invoice);
        $customerWhatsAppSent = $this->whatsAppService->sendQuotationAndPaymentLink($invoice);

        Log::info('Invoice payment link delivery completed', [
            'invoice' => $invoice->invoice_number,
            'customer_email_sent' => $customerEmailSent,
            'admin_email_sent' => $adminEmailSent,
            'customer_whatsapp_sent' => $customerWhatsAppSent,
        ]);

        if (!$customerEmailSent) {
            return back()->withErrors([
                'notification' => "Invoice {$invoice->invoice_number} was created, but the customer email was not accepted by the mail server. Please contact technical support before creating another invoice.",
            ]);
        }

        $message = "Payment link {$invoice->invoice_number} emailed to {$quote->email}.";
        if ($adminEmailSent) {
            $message .= ' A copy was also emailed to dispatch.';
        }

        return back()->with('success', $message);
    }
}
