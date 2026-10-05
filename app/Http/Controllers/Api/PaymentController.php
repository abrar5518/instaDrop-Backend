<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendPaymentNotifications;
use App\Models\Invoice;
use App\Services\PayPalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class PaymentController extends Controller
{
    public function __construct(private PayPalService $payPalService) {}

    /**
     * Fetch invoice details by payment token.
     */
    public function show($token)
    {
        $invoice = Invoice::with('order')
            ->where('payment_token', $token)
            ->first();

        if (! $invoice) {
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
            'paypal_client_id' => $this->payPalClientId(),
            'currency' => 'GBP',
        ])->header('Cache-Control', 'private, no-store');
    }

    /**
     * Process online payment transaction.
     */
    public function createPayPalOrder(Request $request)
    {
        $validated = $request->validate([
            'payment_token' => 'required|string|max:200',
        ]);

        $invoice = Invoice::with('order')
            ->where('payment_token', $validated['payment_token'])
            ->first();

        if (! $invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found.',
            ], 404);
        }

        abort_if($invoice->status === 'paid', 409, 'Invoice has already been paid.');
        try {
            $order = $this->payPalService->createOrder($invoice->invoice_number, number_format((float) $invoice->total_amount, 2, '.', ''));
            if (! is_string($order['id'] ?? null) || $order['id'] === '') {
                throw new \RuntimeException('PayPal did not return an order ID.');
            }

            $invoice->forceFill(['paypal_order_id' => $order['id']])->save();

            return response()->json(['success' => true, 'order_id' => $order['id']]);
        } catch (Throwable $e) {
            Log::error('PayPal order creation failed', ['invoice' => $invoice->invoice_number, 'message' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'PayPal checkout is temporarily unavailable. Please contact dispatch.'], 502);
        }
    }

    public function capturePayPalOrder(Request $request)
    {
        $validated = $request->validate([
            'payment_token' => 'required|string|max:200',
            'paypal_order_id' => ['required', 'string', 'max:100', 'regex:/^[A-Z0-9]+$/i'],
        ]);
        $invoice = Invoice::with('order')->where('payment_token', $validated['payment_token'])->firstOrFail();
        if ($invoice->status === 'paid') {
            return response()->json(['success' => true, 'message' => 'Invoice has already been paid.', 'invoice_number' => $invoice->invoice_number]);
        }

        if (filled($invoice->paypal_order_id)
            && ! hash_equals((string) $invoice->paypal_order_id, $validated['paypal_order_id'])) {
            Log::warning('PayPal capture order did not match the order stored for the invoice', [
                'invoice' => $invoice->invoice_number,
                'stored_paypal_order_id' => $invoice->paypal_order_id,
                'submitted_paypal_order_id' => $validated['paypal_order_id'],
            ]);

            return response()->json([
                'success' => false,
                'message' => 'This PayPal payment does not match the invoice. Please contact dispatch before trying again.',
            ], 422);
        }

        $captureFailed = false;
        try {
            $capture = $this->payPalService->captureOrder($validated['paypal_order_id']);
        } catch (Throwable $e) {
            $captureFailed = true;
            Log::warning('PayPal capture response failed; checking the order before reporting an error', [
                'invoice' => $invoice->invoice_number,
                'paypal_order_id' => $validated['paypal_order_id'],
                'message' => $e->getMessage(),
            ]);

            try {
                $capture = $this->payPalService->getOrder($validated['paypal_order_id']);
            } catch (Throwable $lookupError) {
                Log::error('PayPal capture and order recovery both failed', [
                    'invoice' => $invoice->invoice_number,
                    'paypal_order_id' => $validated['paypal_order_id'],
                    'message' => $lookupError->getMessage(),
                ]);

                return response()->json(['success' => false, 'message' => 'PayPal could not confirm this payment. Please refresh the invoice before trying again or contact dispatch.'], 502);
            }
        }

        $captureData = $this->verifiedCapture($capture, $invoice, $validated['paypal_order_id']);
        if (! $captureData && ! $captureFailed) {
            Log::warning('PayPal capture response was incomplete; checking the order before reporting an error', $this->verificationContext(
                $capture,
                $invoice,
                $validated['paypal_order_id'],
            ));

            try {
                $capture = $this->payPalService->getOrder($validated['paypal_order_id']);
                $captureData = $this->verifiedCapture($capture, $invoice, $validated['paypal_order_id']);
            } catch (Throwable $lookupError) {
                Log::error('PayPal order lookup after an incomplete capture response failed', [
                    'invoice' => $invoice->invoice_number,
                    'paypal_order_id' => $validated['paypal_order_id'],
                    'message' => $lookupError->getMessage(),
                ]);
            }
        }

        if (! $captureData) {
            Log::warning('PayPal payment failed verification', $this->verificationContext(
                $capture,
                $invoice,
                $validated['paypal_order_id'],
            ));

            return response()->json([
                'success' => false,
                'message' => 'PayPal payment is still being confirmed. Do not pay again. Please refresh shortly or contact dispatch.',
            ], 422);
        }

        $paymentRecorded = DB::transaction(function () use ($invoice, $captureData) {
            $lockedInvoice = Invoice::query()->lockForUpdate()->findOrFail($invoice->id);
            if ($lockedInvoice->status === 'paid') {
                return false;
            }

            $lockedInvoice->update(['status' => 'paid', 'payment_method' => 'paypal', 'payment_transaction_id' => $captureData['id'], 'paid_at' => now()]);
            $lockedInvoice->order?->update(['status' => 'paid']);

            return true;
        });
        // Confirmation delivery must never turn a completed payment into a checkout error.
        if ($paymentRecorded) {
            SendPaymentNotifications::dispatchAfterResponse($invoice->id);
        }

        return response()->json(['success' => true, 'message' => 'Payment completed successfully.', 'invoice_number' => $invoice->invoice_number]);
    }

    private function verifiedCapture(array $capture, Invoice $invoice, string $paypalOrderId): ?array
    {
        $captureData = data_get($capture, 'purchase_units.0.payments.captures.0');
        $amount = data_get($captureData, 'amount.value');

        if (! is_array($captureData) || ! is_numeric($amount)) {
            return null;
        }

        $optionalInvoiceIds = array_values(array_filter([
            data_get($capture, 'purchase_units.0.invoice_id'),
            data_get($captureData, 'invoice_id'),
        ], fn ($value) => is_string($value) && $value !== ''));
        $optionalInvoiceIdsMatch = count(array_filter(
            $optionalInvoiceIds,
            fn (string $value) => ! hash_equals($invoice->invoice_number, $value),
        )) === 0;

        $verified = is_string($capture['id'] ?? null)
            && hash_equals($paypalOrderId, $capture['id'])
            && ($capture['status'] ?? null) === 'COMPLETED'
            && ($captureData['status'] ?? null) === 'COMPLETED'
            && data_get($captureData, 'amount.currency_code') === 'GBP'
            && bccomp((string) $amount, (string) $invoice->total_amount, 2) === 0
            && data_get($capture, 'purchase_units.0.reference_id') === $invoice->invoice_number
            && $optionalInvoiceIdsMatch
            && is_string($captureData['id'] ?? null)
            && $captureData['id'] !== '';

        return $verified ? $captureData : null;
    }

    private function verificationContext(array $capture, Invoice $invoice, string $paypalOrderId): array
    {
        return [
            'invoice' => $invoice->invoice_number,
            'submitted_paypal_order_id' => $paypalOrderId,
            'response_paypal_order_id' => $capture['id'] ?? null,
            'order_status' => $capture['status'] ?? null,
            'capture_status' => data_get($capture, 'purchase_units.0.payments.captures.0.status'),
            'amount' => data_get($capture, 'purchase_units.0.payments.captures.0.amount.value'),
            'currency' => data_get($capture, 'purchase_units.0.payments.captures.0.amount.currency_code'),
            'reference_id' => data_get($capture, 'purchase_units.0.reference_id'),
            'purchase_unit_invoice_id' => data_get($capture, 'purchase_units.0.invoice_id'),
            'capture_invoice_id' => data_get($capture, 'purchase_units.0.payments.captures.0.invoice_id'),
        ];
    }

    private function payPalClientId(): ?string
    {
        try {
            return $this->payPalService->clientId();
        } catch (Throwable) {
            return null;
        }
    }
}
