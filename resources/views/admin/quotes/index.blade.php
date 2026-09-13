@extends('admin.layout')

@section('content')
<div class="space-y-8">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900 font-display">Quote Requests & Quotation Generator</h1>
        <p class="text-xs text-slate-500">Review customer requests list below. Click on any row to open its dedicated 11-field detail page & quotation generator.</p>
    </div>

    <!-- Clean Overview Table of All Quotes -->
    <div class="bg-white border border-slate-200/80 rounded-3xl p-6 sm:p-8 shadow-xs space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-extrabold text-slate-900 font-display">Incoming Quote Requests ({{ $quotes->count() }})</h2>
            <span class="text-xs font-semibold text-slate-500">Click any row to inspect 11-field details</span>
        </div>

        <div class="overflow-x-auto text-xs">
            <table class="w-full text-left">
                <thead class="bg-slate-50 text-slate-500 text-[11px] uppercase font-bold border-b border-slate-200">
                    <tr>
                        <th class="p-3.5">Quote #</th>
                        <th class="p-3.5">Customer Name</th>
                        <th class="p-3.5">Route</th>
                        <th class="p-3.5">Vehicle</th>
                        <th class="p-3.5">Collection Slot</th>
                        <th class="p-3.5">Contact Preference</th>
                        <th class="p-3.5">Status</th>
                        <th class="p-3.5">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @foreach ($quotes as $quote)
                        <tr class="hover:bg-slate-50 transition-colors cursor-pointer" onclick="window.location.href='{{ route('admin.quotes.show', $quote->id) }}'">
                            <td class="p-3.5 font-extrabold text-[#0a192f]">{{ $quote->quote_number }}</td>
                            <td class="p-3.5 font-bold text-slate-900">{{ $quote->first_name }} {{ $quote->last_name }}</td>
                            <td class="p-3.5 font-semibold text-slate-800">{{ $quote->collection_postcode }} ➔ {{ $quote->delivery_postcode }}</td>
                            <td class="p-3.5 capitalize font-semibold text-[#0a192f]">{{ str_replace('_', ' ', $quote->vehicle_type) }}</td>
                            <td class="p-3.5 whitespace-nowrap font-semibold text-blue-700">{{ $quote->collection_schedule }}</td>
                            <td class="p-3.5 font-bold uppercase text-emerald-700">{{ $quote->contact_preference }}</td>
                            <td class="p-3.5">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase {{ $quote->status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                                    {{ $quote->status }}
                                </span>
                            </td>
                            <td class="p-3.5">
                                <a href="{{ route('admin.quotes.show', $quote->id) }}" class="px-3.5 py-1.5 bg-[#0a192f] text-white rounded-xl font-bold text-xs hover:bg-[#051329] transition-colors inline-flex items-center gap-1.5 shadow-xs">
                                    <span>View Details & Price</span>
                                    <span>➔</span>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
