<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuoteRequest;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\SystemSetting;
use App\Services\WhatsAppService;
use App\Services\EmailNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Jobs\SendInvoiceNotifications;

class InvoiceController extends Controller
{
    protected WhatsAppService $whatsAppService;
    protected EmailNotificationService $emailService;

    public function __construct(WhatsAppService $whatsAppService, EmailNotificationService $emailService)
    {
        $this->whatsAppService = $whatsAppService;
        $this->emailService = $emailService;
    }

    /**
     * Admin action to enter selling price & generate invoice + payment link
     */
    public function generate(Request $request, QuoteRequest $quote)
    {
        $validated = $request->validate([
            'quoted_selling_price' => 'required|numeric|min:1',
            'pickup_address'       => 'required|string',
            'delivery_address'     => 'required|string',
            'carrier_name'         => 'nullable|string',
        ]);

        $setting = SystemSetting::first();
        $vatRate = $setting ? ($setting->vat_rate / 100) : 0.20;

        $subtotal = $validated['quoted_selling_price'];
        $vatAmount = round($subtotal * $vatRate, 2);
        $totalAmount = round($subtotal + $vatAmount, 2);

        // 1. Create or update parent Order
        $trackingNumber = 'INSTA-' . strtoupper(Str::random(6));

        $order = Order::create([
            'quote_request_id'         => $quote->id,
            'tracking_number'          => $trackingNumber,
            'customer_name'            => $quote->full_name,
            'customer_email'           => $quote->email,
            'customer_phone'           => $quote->phone,
            'preferred_contact_method' => $quote->contact_preference,
            'pickup_address'           => $validated['pickup_address'],
            'delivery_address'          => $validated['delivery_address'],
            'vehicle_type'             => $quote->vehicle_type,
            'carrier_name'             => $validated['carrier_name'] ?? 'InstaDrop Fleet',
            'quoted_selling_price'     => $totalAmount,
            'status'                   => 'pending_payment',
        ]);

        // 2. Create Invoice with secure payment token
        $invoiceNumber = 'INV-' . date('Y') . '-' . rand(1000, 9999);
        $paymentToken = 'PAY-' . Str::uuid();

        $invoice = Invoice::create([
            'order_id'       => $order->id,
            'invoice_number' => $invoiceNumber,
            'payment_token'  => $paymentToken,
            'subtotal'       => $subtotal,
            'vat_amount'     => $vatAmount,
            'total_amount'   => $totalAmount,
            'status'         => 'unpaid',
        ]);

        // Update Quote Request Status
        $quote->update(['status' => 'quoted']);

        // 3. Automatically Dispatch WhatsApp & Email Payment Link
        SendInvoiceNotifications::dispatch($invoice->id)->afterResponse();

        return redirect()->back()->with('success', "Invoice {$invoiceNumber} created and payment link sent to {$order->customer_name} via {$order->preferred_contact_method}.");
    }
}
