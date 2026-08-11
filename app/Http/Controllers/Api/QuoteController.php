<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuoteRequest;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QuoteController extends Controller
{
    /**
     * Handle incoming quote request with all 11 form fields from Next.js frontend.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name'          => 'required|string|max:100',
            'last_name'           => 'required|string|max:100',
            'email'               => 'required|email|max:100',
            'phone'               => 'required|string|max:50',
            'contact_preference'  => 'required|in:whatsapp,email,phone_call',
            'collection_postcode' => 'required|string|max:20',
            'delivery_postcode'   => 'required|string|max:20',
            'vehicle_type'        => 'required|string|max:50',
            'timescale'           => 'required|string|max:50',
            'enquiry_type'        => 'required|in:business,personal',
            'additional_info'     => 'nullable|string',
        ]);

        $quoteNumber = 'Q-' . strtoupper(Str::random(6));

        $quote = QuoteRequest::create([
            'quote_number'        => $quoteNumber,
            'first_name'          => $validated['first_name'],
            'last_name'           => $validated['last_name'],
            'email'               => $validated['email'],
            'phone'               => $validated['phone'],
            'contact_preference'  => $validated['contact_preference'],
            'collection_postcode' => strtoupper($validated['collection_postcode']),
            'delivery_postcode'   => strtoupper($validated['delivery_postcode']),
            'vehicle_type'        => $validated['vehicle_type'],
            'timescale'           => $validated['timescale'],
            'enquiry_type'        => $validated['enquiry_type'],
            'additional_info'     => $validated['additional_info'] ?? null,
            'status'              => 'pending',
        ]);

        $setting = SystemSetting::first();
        $adminWhatsApp = $setting ? $setting->admin_whatsapp_number : '+448001234455';

        return response()->json([
            'success' => true,
            'message' => 'We have received your delivery quote request and will contact you shortly.',
            'quote_number' => $quote->quote_number,
            'preferred_contact' => $quote->contact_preference,
            'admin_whatsapp' => $adminWhatsApp,
        ], 201);
    }
}
