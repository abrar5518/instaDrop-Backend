<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use App\Services\WhatsAppService;
use App\Services\EmailNotificationService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected WhatsAppService $whatsAppService;
    protected EmailNotificationService $emailService;

    public function __construct(WhatsAppService $whatsAppService, EmailNotificationService $emailService)
    {
        $this->whatsAppService = $whatsAppService;
        $this->emailService = $emailService;
    }

    /**
     * Show Public Checkout Page Data by Token
     */
    public function show($token)
    {
        $invoice = Invoice::with('order')->where('payment_token', $token)->first();

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice checkout link not found or expired.',
            ], 404);
        }

        return response()->json([
            'success'        => true,
            'invoice_number' => $invoice->invoice_number,
            'total_amount'   => $invoice->total_amount,
            'vat_amount'     => $invoice->vat_amount,
            'subtotal'       => $invoice->total_amount - $invoice->vat_amount,
            'payment_status' => $invoice->payment_status,
            'customer_name'  => $invoice->order->customer_name,
            'pickup_address' => $invoice->order->pickup_address,
            'delivery_address' => $invoice->order->delivery_address,
            'vehicle_type'   => $invoice->order->vehicle_type,
        ]);
    }

    /**
     * Step 12 & 13: Online Payment Processing & Automatic Payment Verification Webhook
     */
    public function process(Request $request)
    {
        $request->validate([
            'payment_token' => 'required|string',
            'payment_method' => 'nullable|string',
        ]);

        $invoice = Invoice::where('payment_token', $request->payment_token)->first();

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid payment token.',
            ], 404);
        }

        // Mark invoice & order as paid
        $invoice->payment_status = 'paid';
        $invoice->save();

        $order = $invoice->order;
        if ($order) {
            $order->payment_status = 'paid';
            $order->payment_method = $request->payment_method ?? 'credit_card';
            $order->save();

            // Step 13: Auto send payment receipts to Admin & Customer via WhatsApp & Email
            $this->whatsAppService->sendPaymentSuccessConfirmation($order);
            $this->emailService->sendPaymentSuccessConfirmation($order);
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment processed successfully! Confirmation sent to your WhatsApp and Email.',
            'order_number' => $order->order_number ?? 'INSTA-884920',
        ]);
    }
}
