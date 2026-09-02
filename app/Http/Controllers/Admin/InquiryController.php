<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use Illuminate\Http\Request;

class InquiryController extends Controller
{
    public function index()
    {
        $inquiries = Inquiry::latest()->paginate(20);
        return view('admin.inquiries.index', compact('inquiries'));
    }

    public function updateStatus(Request $request, Inquiry $inquiry)
    {
        $validated = $request->validate(['status' => 'required|in:new,in_progress,closed']);
        $inquiry->update($validated);
        return redirect()->back()->with('success', 'Inquiry status updated.');
    }
}
