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

    public function create(Order $order)
    {
        return view('admin.pods.create', compact('order'));
    }

    public function store(Request $request, Order $order)
    {
        $validated = $request->validate([
            'recipient_name' => 'required|string|max:100',
            'signature_file' => 'nullable|file|mimes:png,jpg,jpeg,pdf|max:5120',
            'photo_file'     => 'nullable|file|mimes:png,jpg,jpeg,pdf|max:5120',
            'delivered_at'   => 'required|date',
        ]);

        $signaturePath = null;
        if ($request->hasFile('signature_file')) {
            $signaturePath = $request->file('signature_file')->store('pods', 'public');
        }

        $photoPath = null;
        if ($request->hasFile('photo_file')) {
            $photoPath = $request->file('photo_file')->store('pods', 'public');
        }

        $pod = Pod::create([
            'order_id'       => $order->id,
            'recipient_name' => $validated['recipient_name'],
            'signature_path' => $signaturePath,
            'photo_path'     => $photoPath,
            'delivered_at'   => $validated['delivered_at'],
        ]);

        // Update Order status to Delivered
        $order->update(['status' => 'delivered']);

        // Automatically dispatch POD notifications via WhatsApp & Email
        $this->whatsAppService->sendPodNotification($order, $pod);
        $this->emailService->sendPodEmail($order, $pod);

        return redirect()->route('admin.orders.index')->with('success', "POD uploaded for Order #{$order->tracking_number}. Notification automatically sent to customer!");
    }
}
