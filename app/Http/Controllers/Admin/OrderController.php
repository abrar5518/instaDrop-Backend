<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\WhatsAppService;
use App\Services\EmailNotificationService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected WhatsAppService $whatsAppService;
    protected EmailNotificationService $emailService;

    public function __construct(WhatsAppService $whatsAppService, EmailNotificationService $emailService)
    {
        $this->whatsAppService = $whatsAppService;
        $this->emailService = $emailService;
    }

    public function index()
    {
        $orders = Order::with(['invoice', 'pod'])->latest()->paginate(15);
        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Step 15 & 16: Update Delivery Status & Dispatch Auto WhatsApp/Email Notification
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string',
        ]);

        $order = Order::findOrFail($id);
        $order->status = $request->status;
        $order->save();

        // Step 16: Auto send status update notification to customer
        $this->whatsAppService->sendStatusUpdateNotification($order, $request->status);
        $this->emailService->sendStatusUpdateNotification($order, $request->status);

        return redirect()->back()->with('success', "Order #{$order->order_number} status updated to " . strtoupper($request->status) . " and notification dispatched to customer!");
    }

    /**
     * Step 12: Manual Payment Status Override Switcher (Mark as Paid / Mark as Unpaid)
     */
    public function updatePaymentStatus(Request $request, $id)
    {
        $request->validate([
            'payment_status' => 'required|string|in:paid,unpaid,refunded',
        ]);

        $order = Order::findOrFail($id);
        $order->payment_status = $request->payment_status;
        $order->save();

        if ($request->payment_status === 'paid') {
            // Auto send payment confirmation receipt to Admin & Customer
            $this->whatsAppService->sendPaymentSuccessConfirmation($order);
            $this->emailService->sendPaymentSuccessConfirmation($order);
        }

        return redirect()->back()->with('success', "Order #{$order->order_number} payment status manually updated to " . strtoupper($request->payment_status) . "!");
    }

    /**
     * Step 14: Assign Driver Details
     */
    public function assignDriver(Request $request, $id)
    {
        $request->validate([
            'driver_name'        => 'required|string|max:100',
            'driver_phone'       => 'required|string|max:50',
            'driver_vehicle_reg' => 'required|string|max:20',
        ]);

        $order = Order::findOrFail($id);
        $order->driver_name = $request->driver_name;
        $order->driver_phone = $request->driver_phone;
        $order->driver_vehicle_reg = $request->driver_vehicle_reg;
        $order->status = 'driver_assigned';
        $order->save();

        return redirect()->back()->with('success', "Driver {$request->driver_name} assigned to Order #{$order->order_number} successfully!");
    }
}
