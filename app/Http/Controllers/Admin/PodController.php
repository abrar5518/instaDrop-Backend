<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Pod;
use App\Services\WhatsAppService;
use App\Services\EmailNotificationService;
use Illuminate\Http\Request;

class PodController extends Controller
{
    protected WhatsAppService $whatsAppService;
    protected EmailNotificationService $emailService;

    public function __construct(WhatsAppService $whatsAppService, EmailNotificationService $emailService)
    {
        $this->whatsAppService = $whatsAppService;
        $this->emailService = $emailService;
    }

    /**
     * Step 17 & 18: Upload Proof of Delivery (POD) & Auto-Dispatch Certificate to Customer
     */
    public function store(Request $request, $orderId)
    {
        $request->validate([
            'recipient_name' => 'required|string|max:100',
            'pod_photo'      => 'nullable|image|max:5048',
            'notes'          => 'nullable|string',
        ]);

        $order = Order::findOrFail($orderId);

        $photoPath = null;
        if ($request->hasFile('pod_photo')) {
            $photoPath = $request->file('pod_photo')->store('pods', 'public');
        }

        $pod = Pod::updateOrCreate(
            ['order_id' => $order->id],
            [
                'recipient_name' => $request->recipient_name,
                'signature_url'  => $photoPath ? asset('storage/' . $photoPath) : null,
                'photo_url'      => $photoPath ? asset('storage/' . $photoPath) : null,
                'delivered_at'   => now(),
                'notes'          => $request->notes,
            ]
        );

        // Update order status to delivered
        $order->status = 'delivered';
        $order->save();

        // Step 18: Auto send digital POD certificate to customer via WhatsApp & Email
        $this->whatsAppService->sendPodCertificate($order, $pod);
        $this->emailService->sendPodCertificate($order, $pod);

        return redirect()->back()->with('success', "Proof of Delivery (POD) uploaded successfully and digital certificate dispatched to customer!");
    }
}
