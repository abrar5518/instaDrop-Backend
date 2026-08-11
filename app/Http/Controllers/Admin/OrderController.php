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

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending_payment,paid,dispatched,collected,in_transit,delivered,cancelled',
        ]);

        $order->update(['status' => $validated['status']]);

        // Automatically dispatch WhatsApp & Email status update notification to customer
        $this->whatsAppService->sendStatusUpdateNotification($order);

        return redirect()->back()->with('success', "Order #{$order->tracking_number} status updated to " . strtoupper($order->status) . ". Notification sent to customer.");
    }
}
