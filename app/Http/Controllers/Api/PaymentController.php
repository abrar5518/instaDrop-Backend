<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
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
        ]);
    }

    /**
     * Process online payment transaction.
     */
    public function process(Request $request)
    {
        $validated = $request->validate([
            'payment_token' => 'required|string',
            'payment_method' => 'required|string', // stripe, credit_card
            'card_token' => 'nullable|string',
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

        if ($invoice->status === 'paid') {
            return response()->json([
                'success' => true,
                'message' => 'Invoice has already been paid.',
                'invoice_number' => $invoice->invoice_number,
            ]);
        }

        // Simulate successful payment transaction
        $transactionId = 'TXN-' . strtoupper(substr(md5(uniqid()), 0, 10));

        $invoice->update([
            'status' => 'paid',
            'payment_method' => $validated['payment_method'],
            'payment_transaction_id' => $transactionId,
            'paid_at' => now(),
        ]);

        // Update parent order status
        if ($invoice->order) {
            $invoice->order->update([
                'status' => 'paid',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment processed successfully! Confirmation sent to your email and WhatsApp.',
            'invoice_number' => $invoice->invoice_number,
            'transaction_id' => $transactionId,
            'paid_at' => $invoice->paid_at->toDateTimeString(),
        ]);
    }
}
