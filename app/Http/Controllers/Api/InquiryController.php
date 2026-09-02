<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use Illuminate\Http\Request;
use App\Jobs\SendInquiryNotifications;

class InquiryController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'inquiry_type' => 'required|in:contact,business_account',
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150',
            'phone' => 'required|string|max:50',
            'company_name' => 'nullable|required_if:inquiry_type,business_account|string|max:150',
            'company_registration' => 'nullable|required_if:inquiry_type,business_account|string|max:100',
            'monthly_deliveries' => 'nullable|string|max:100',
            'subject' => 'nullable|string|max:150',
            'message' => 'nullable|string|max:5000',
        ]);

        $inquiry = Inquiry::create($validated);
        SendInquiryNotifications::dispatch($inquiry->id)->afterResponse();

        return response()->json([
            'success' => true,
            'message' => $inquiry->inquiry_type === 'business_account'
                ? 'Your corporate account application has been received.'
                : 'Your message has been received by our operations team.',
            'reference' => 'INQ-' . str_pad((string) $inquiry->id, 6, '0', STR_PAD_LEFT),
        ], 201);
    }
}
