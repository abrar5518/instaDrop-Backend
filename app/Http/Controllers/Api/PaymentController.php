<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\PayPalService;
use App\Services\EmailNotificationService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class PaymentController extends Controller
{
    public function __construct(private PayPalService $payPalService)
    {
    }
    /**
     * Fetch invoice details by payment token.
     */
    public function show($token)
    {
        $invoice = Invoice::with('order')
            ->where('payment_token', $token)
            ->first();

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired payment link.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'invoice_number' => $invoice->invoice_number,
            'customer_name' => $invoice->order->customer_name,
            'customer_email' => $invoice->order->customer_email,
            'pickup_address' => $invoice->order->pickup_address,
            'delivery_address' => $invoice->order->delivery_address,
            'vehicle_type' => $invoice->order->vehicle_type,
            'subtotal' => $invoice->subtotal,
            'vat_amount' => $invoice->vat_amount,
            'total_amount' => $invoice->total_amount,
            'status' => $invoice->status,
            'paid_at' => $invoice->paid_at,
            'payment_token' => $invoice->payment_token,
            'paypal_client_id' => config('services.paypal.client_id'),
            'currency' => 'GBP',
        ]);
    }

    /**
     * Process online payment transaction.
     */
    public function createPayPalOrder(Request $request)
    {
        $validated = $request->validate([
            'payment_token' => 'required|string',
        ]);

        $invoice = Invoice::with('order')
            ->where('payment_token', $validated['payment_token'])
            ->first();

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found.',
            ], 404);
        }

        abort_if($invoice->status === 'paid', 409, 'Invoice has already been paid.');
        try {
            $order = $this->payPalService->createOrder($invoice->invoice_number, number_format((float) $invoice->total_amount, 2, '.', ''));
            return response()->json(['success' => true, 'order_id' => $order['id']]);
        } catch (Throwable $e) {
            Log::error('PayPal order creation failed', ['invoice' => $invoice->invoice_number, 'message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'PayPal checkout is temporarily unavailable. Please contact dispatch.'], 502);
        }
    }

    public function capturePayPalOrder(Request $request, EmailNotificationService $email, WhatsAppService $whatsApp)
    {
        $validated = $request->validate(['payment_token' => 'required|string', 'paypal_order_id' => 'required|string|max:100']);
        $invoice = Invoice::with('order')->where('payment_token', $validated['payment_token'])->firstOrFail();
        if ($invoice->status === 'paid') {
            return response()->json(['success' => true, 'message' => 'Invoice has already been paid.', 'invoice_number' => $invoice->invoice_number]);
        }

        try {
            $capture = $this->payPalService->captureOrder($validated['paypal_order_id']);
        } catch (Throwable $e) {
            Log::error('PayPal capture failed', ['invoice' => $invoice->invoice_number, 'message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'PayPal could not confirm this payment. Please try again or contact dispatch.'], 502);
        }
        $captureData = data_get($capture, 'purchase_units.0.payments.captures.0');
        $amount = data_get($captureData, 'amount.value');
        $currency = data_get($captureData, 'amount.currency_code');
        $reference = data_get($capture, 'purchase_units.0.reference_id');
        $paypalInvoice = data_get($capture, 'purchase_units.0.invoice_id');
        abort_unless(
            ($capture['status'] ?? null) === 'COMPLETED'
            && $currency === 'GBP'
            && bccomp((string) $amount, (string) $invoice->total_amount, 2) === 0
            && $reference === $invoice->invoice_number
            && $paypalInvoice === $invoice->invoice_number,
            422,
            'PayPal payment could not be verified.'
        );

        DB::transaction(function () use ($invoice, $captureData) {
            $invoice->update(['status' => 'paid', 'payment_method' => 'paypal', 'payment_transaction_id' => $captureData['id'], 'paid_at' => now()]);
            $invoice->order?->update(['status' => 'paid']);
        });
        $email->sendPaymentConfirmationEmail($invoice->fresh('order'));
        $whatsApp->sendPaymentConfirmation($invoice->fresh('order'));

        return response()->json(['success' => true, 'message' => 'Payment completed successfully.', 'invoice_number' => $invoice->invoice_number]);
    }
}
