<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuoteRequest;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class QuoteRequestController extends Controller
{
    public function index()
    {
        $quotes = QuoteRequest::latest()->paginate(15);
        return view('admin.quotes.index', compact('quotes'));
    }

    public function show(QuoteRequest $quote)
    {
        $vatRate = (float) (SystemSetting::query()->value('vat_rate') ?? 20);

        return view('admin.quotes.show', compact('quote', 'vatRate'));
    }

    public function updateStatus(Request $request, QuoteRequest $quote)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,quoted,converted,cancelled',
            'admin_notes' => 'nullable|string',
        ]);

        $quote->update($validated);

        return redirect()->back()->with('success', 'Quote status updated successfully.');
    }
}
