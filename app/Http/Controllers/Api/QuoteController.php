<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuoteRequest;
use App\Services\WhatsAppService;
use App\Services\EmailNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QuoteController extends Controller
{
    protected WhatsAppService $whatsAppService;
    protected EmailNotificationService $emailService;

    public function __construct(WhatsAppService $whatsAppService, EmailNotificationService $emailService)
    {
        $this->whatsAppService = $whatsAppService;
        $this->emailService = $emailService;
    }

    /**
     * Store incoming quote request from Next.js Speedy Quote Form (All 11 Fields)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name'            => 'required|string|max:100',
            'last_name'             => 'required|string|max:100',
            'email'                 => 'required|email|max:150',
            'phone'                 => 'required|string|max:50',
            'contact_preference'    => 'required|string',
            'collection_postcode'   => 'required|string|max:20',
            'delivery_postcode'     => 'required|string|max:20',
            'vehicle_type'          => 'required|string',
            'timescale'             => 'required|string',
            'enquiry_type'          => 'required|string',
            'additional_info'       => 'nullable|string',
        ]);

        // Auto-generate quote number (e.g. Q-88492)
        $validated['quote_number'] = 'Q-' . rand(10000, 99999);
        $validated['status'] = 'pending';

        $quote = QuoteRequest::create($validated);

        // Step 3 & 4: Dual Notifications Trigger
        // 1. Admin Business WhatsApp Alert
        $this->whatsAppService->sendAdminNewQuoteAlert($quote);
        $this->emailService->sendAdminNewQuoteAlert($quote);

        // 2. Customer Automatic Acknowledgment ("We have received your delivery request...")
        $this->whatsAppService->sendCustomerQuoteAcknowledgment($quote);
        $this->emailService->sendCustomerQuoteAcknowledgment($quote);

        return response()->json([
            'success' => true,
            'quote_number' => $quote->quote_number,
            'message' => "We have received your delivery request (#{$quote->quote_number}) and will contact you shortly.",
            'preferred_contact' => $quote->contact_preference,
        ], 201);
    }
}
