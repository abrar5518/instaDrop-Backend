<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuoteRequest;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\Pod;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_quotes_today' => QuoteRequest::whereDate('created_at', today())->count(),
            'pending_quotes'     => QuoteRequest::where('status', 'pending')->count(),
            'unpaid_invoices'     => Invoice::where('status', 'unpaid')->count(),
            'active_deliveries'  => Order::whereIn('status', ['dispatched', 'collected', 'in_transit'])->count(),
            'completed_pods'     => Pod::whereDate('created_at', today())->count(),
        ];

        $recentQuotes = QuoteRequest::latest()->take(10)->get();
        $recentOrders = Order::latest()->take(10)->get();

        return view('admin.dashboard', compact('stats', 'recentQuotes', 'recentOrders'));
    }
}
