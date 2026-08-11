<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderTrackingController extends Controller
{
    /**
     * Handle live tracking lookup by tracking number (e.g. INSTA-884920).
     */
    public function show($tracking_number)
    {
        $trackingNumberClean = strtoupper(trim($tracking_number));

        $order = Order::with('pod')
            ->where('tracking_number', $trackingNumberClean)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid tracking number. Please check your delivery confirmation email or WhatsApp.',
            ], 404);
        }

        $podData = null;
        if ($order->pod) {
            $podData = [
                'recipient_name' => $order->pod->recipient_name,
                'signature_url' => $order->pod->signature_path ? asset('storage/' . $order->pod->signature_path) : null,
                'photo_url' => $order->pod->photo_path ? asset('storage/' . $order->pod->photo_path) : null,
                'delivered_at' => $order->pod->delivered_at,
            ];
        }

        return response()->json([
            'success' => true,
            'tracking_number' => $order->tracking_number,
            'customer_name' => $order->customer_name,
            'vehicle_type' => $order->vehicle_type,
            'status' => $order->status,
            'pickup_address' => $order->pickup_address,
            'delivery_address' => $order->delivery_address,
            'carrier_name' => $order->carrier_name ?? 'InstaDrop Express Fleet',
            'pod' => $podData,
        ]);
    }
}
