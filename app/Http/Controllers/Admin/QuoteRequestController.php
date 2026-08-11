<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuoteRequest;
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
        return view('admin.quotes.show', compact('quote'));
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
