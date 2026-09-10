<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuoteRequest;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Jobs\SendQuoteNotifications;
use App\Rules\UkPhoneNumber;
use Illuminate\Validation\Rule;

class QuoteController extends Controller
{
    /**
     * Handle a quote request with the requested collection schedule in UK local time.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name'          => 'required|string|max:100',
            'last_name'           => 'required|string|max:100',
            'email'               => 'required|email|max:100',
            'phone'               => ['required', 'string', 'max:20', new UkPhoneNumber],
            'contact_preference'  => 'required|in:whatsapp,email,phone_call',
            'collection_postcode' => 'required|string|max:20',
            'delivery_postcode'   => 'required|string|max:20',
            'vehicle_type'        => ['required', Rule::in(['courier_car', 'small_van', 'medium_van', 'large_van', 'luton_tail_lift'])],
            'timescale'           => 'required|string|max:50',
            'collection_date'    => 'required|date_format:Y-m-d',
            'collection_time'    => 'required|date_format:H:i',
            'additional_info'     => 'nullable|string',
        ]);

        $requested = $validated['collection_date'].' '.$validated['collection_time'];
        $collectionAt = \Carbon\CarbonImmutable::createFromFormat('!Y-m-d H:i', $requested, 'Europe/London');
        if ($collectionAt->format('Y-m-d H:i') !== $requested) {
            throw \Illuminate\Validation\ValidationException::withMessages(['collection_time' => 'Please choose a valid UK local time. This time is affected by the clock change.']);
        }
        if ($collectionAt->isPast()) {
            throw \Illuminate\Validation\ValidationException::withMessages(['collection_date' => 'Please choose a collection date and time in the future (UK time).']);
        }

        $quoteNumber = 'Q-' . strtoupper(Str::random(6));

        $quote = QuoteRequest::create([
            'quote_number'        => $quoteNumber,
            'first_name'          => $validated['first_name'],
            'last_name'           => $validated['last_name'],
            'email'               => $validated['email'],
            'phone'               => UkPhoneNumber::normalize($validated['phone']),
            'contact_preference'  => $validated['contact_preference'],
            'collection_postcode' => strtoupper($validated['collection_postcode']),
            'delivery_postcode'   => strtoupper($validated['delivery_postcode']),
            'vehicle_type'        => $validated['vehicle_type'],
            'timescale'           => $validated['timescale'],
            'collection_date'    => $validated['collection_date'],
            'collection_time'    => $validated['collection_time'],
            'additional_info'     => $validated['additional_info'] ?? null,
            'status'              => 'pending',
        ]);

        SendQuoteNotifications::dispatch($quote->id)->afterResponse();

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
