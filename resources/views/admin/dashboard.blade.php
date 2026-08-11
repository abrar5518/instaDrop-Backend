@extends('admin.layout')

@section('content')
<div class="space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 font-display">Dashboard Overview</h1>
            <p class="text-xs text-slate-500">Welcome to InstaDrop Dispatch Control Panel</p>
        </div>
        <a href="/pay/PAY-DEMO" target="_blank" class="px-4 py-2.5 bg-[#0a192f] text-white border border-[#0a192f] rounded-xl text-xs font-bold hover:bg-[#051329] transition-colors shadow-xs">
            🔗 Preview Customer Payment Checkout Page
        </a>
    </div>

    <!-- Quick Stats Cards with Clean Light Theme -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 text-xs">
        <div class="bg-white border border-slate-200/80 p-5 rounded-2xl space-y-1 shadow-xs">
            <p class="text-slate-500 font-semibold">Quotes Today</p>
            <p class="text-2xl font-black text-slate-900 font-display">{{ $stats['total_quotes_today'] ?? 14 }}</p>
        </div>
        <div class="bg-white border border-slate-200/80 p-5 rounded-2xl space-y-1 shadow-xs">
            <p class="text-slate-500 font-semibold">Pending Quotes</p>
            <p class="text-2xl font-black text-amber-600 font-display">{{ $stats['pending_quotes'] ?? 3 }}</p>
        </div>
        <div class="bg-white border border-slate-200/80 p-5 rounded-2xl space-y-1 shadow-xs">
            <p class="text-slate-500 font-semibold">Unpaid Invoices</p>
            <p class="text-2xl font-black text-red-600 font-display">{{ $stats['unpaid_invoices'] ?? 2 }}</p>
        </div>
        <div class="bg-white border border-slate-200/80 p-5 rounded-2xl space-y-1 shadow-xs">
            <p class="text-slate-500 font-semibold">Active Deliveries</p>
            <p class="text-2xl font-black text-blue-600 font-display">{{ $stats['active_deliveries'] ?? 5 }}</p>
        </div>
        <div class="bg-white border border-slate-200/80 p-5 rounded-2xl space-y-1 shadow-xs">
            <p class="text-slate-500 font-semibold">Completed PODs</p>
            <p class="text-2xl font-black text-emerald-600 font-display">{{ $stats['completed_pods'] ?? 12 }}</p>
        </div>
    </div>

    <!-- Recent Quote Requests Table -->
    <div class="bg-white border border-slate-200/80 rounded-3xl p-6 space-y-4 shadow-xs">
        <h2 class="text-base font-extrabold text-slate-900 font-display">Recent Customer Quote Requests</h2>
        
        <div class="overflow-x-auto text-xs">
            <table class="w-full text-left">
                <thead class="bg-slate-50 text-slate-500 text-[11px] uppercase font-bold border-b border-slate-200">
                    <tr>
                        <th class="p-3.5">Quote #</th>
                        <th class="p-3.5">Customer Name</th>
                        <th class="p-3.5">Route</th>
                        <th class="p-3.5">Vehicle</th>
                        <th class="p-3.5">Contact Method</th>
                        <th class="p-3.5">Status</th>
                        <th class="p-3.5">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($recentQuotes as $quote)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="p-3.5 font-extrabold text-[#0a192f]">{{ $quote->quote_number }}</td>
                            <td class="p-3.5 font-bold text-slate-900">{{ $quote->first_name ?? $quote->contact_name }} {{ $quote->last_name }}</td>
                            <td class="p-3.5 font-semibold text-slate-800">{{ $quote->collection_postcode ?? $quote->pickup_postcode }} ➔ {{ $quote->delivery_postcode }}</td>
                            <td class="p-3.5 capitalize">{{ str_replace('_', ' ', $quote->vehicle_type) }}</td>
                            <td class="p-3.5 font-bold uppercase text-emerald-700">{{ $quote->contact_preference ?? $quote->preferred_contact_method }}</td>
                            <td class="p-3.5">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase {{ $quote->status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                                    {{ $quote->status }}
                                </span>
                            </td>
                            <td class="p-3.5">
                                <a href="{{ route('admin.quotes.index') }}" class="text-[#0066ff] hover:underline font-bold">
                                    Set Price & Send Invoice
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-4 text-center text-slate-400">No quote requests received yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
