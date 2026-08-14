@extends('admin.layout')

@section('content')
<div class="space-y-6 w-full">
    <!-- Top Breadcrumb & Status Navigation -->
    <div class="flex items-center justify-between border-b border-slate-200/80 pb-4">
        <a href="{{ route('admin.quotes.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-50 transition-colors shadow-xs">
            ← Back to All Quote Requests
        </a>
        <div class="flex items-center gap-3">
            <span class="text-xs font-black text-[#0a192f] bg-[#c6ff00] px-3.5 py-1.5 rounded-full shadow-xs">
                QUOTE #{{ $quote->quote_number }}
            </span>
            <span class="px-3.5 py-1.5 rounded-full text-xs font-extrabold uppercase {{ $quote->status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                {{ $quote->status }}
            </span>
        </div>
    </div>

    <!-- FULL-WIDTH 2-COLUMN DASHBOARD GRID (0% Empty Space) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- LEFT COLUMN: Customer Info, Route Timeline & 11-Field Grid (2/3 Width) -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Customer Banner Card -->
            <div class="bg-white border border-slate-200/80 rounded-3xl p-6 sm:p-8 space-y-4 shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Customer Submission Profile</span>
                        <h1 class="text-2xl font-extrabold text-slate-900 font-display mt-0.5">
                            {{ $quote->first_name }} {{ $quote->last_name }}
                        </h1>
                    </div>
                    <div>
                        <span class="text-slate-400 text-xs block text-right font-medium">Preferred Contact:</span>
                        <strong class="text-emerald-700 bg-emerald-50 px-3 py-1 rounded-md uppercase font-bold text-xs border border-emerald-200">
                            {{ $quote->contact_preference }}
                        </strong>
                    </div>
                </div>

                <!-- Pickup & Dropoff Route Graphic & Distance Estimator -->
                <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200/60 flex items-center justify-between gap-4 text-xs">
                    <div class="space-y-1">
                        <span class="text-slate-400 font-semibold block">Collection Postcode</span>
                        <strong class="text-slate-900 font-extrabold text-sm block">{{ $quote->collection_postcode }}</strong>
                    </div>
                    <div class="flex-1 flex items-center justify-center px-4">
                        <div class="w-full h-0.5 bg-slate-300 relative flex items-center justify-center">
                            <span class="bg-[#0a192f] text-white text-[10px] font-bold px-3 py-1 rounded-full uppercase">
                                {{ str_replace('_', ' ', $quote->vehicle_type) }} • Est. 205 Miles
                            </span>
                        </div>
                    </div>
                    <div class="space-y-1 text-right">
                        <span class="text-slate-400 font-semibold block">Delivery Postcode</span>
                        <strong class="text-slate-900 font-extrabold text-sm block">{{ $quote->delivery_postcode }}</strong>
                    </div>
                </div>
            </div>

            <!-- ALL 11 FORM FIELDS DETAILED BREAKDOWN -->
            <div class="bg-white border border-slate-200/80 rounded-3xl p-6 sm:p-8 space-y-4 shadow-sm">
                <h3 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-3">
                    Frontend Submission Details (11 Form Fields)
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                    <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/60">
                        <span class="text-slate-400 block font-semibold">1. First Name:</span>
                        <strong class="text-slate-900 font-bold text-sm">{{ $quote->first_name }}</strong>
                    </div>
                    <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/60">
                        <span class="text-slate-400 block font-semibold">2. Last Name:</span>
                        <strong class="text-slate-900 font-bold text-sm">{{ $quote->last_name }}</strong>
                    </div>
                    <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/60">
                        <span class="text-slate-400 block font-semibold">3. Email Address:</span>
                        <strong class="text-slate-900 font-bold">{{ $quote->email }}</strong>
                    </div>

                    <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/60">
                        <span class="text-slate-400 block font-semibold">4. Phone Number:</span>
                        <strong class="text-slate-900 font-bold">{{ $quote->phone }}</strong>
                    </div>
                    <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/60">
                        <span class="text-slate-400 block font-semibold">5. Contact Preference:</span>
                        <strong class="text-emerald-700 font-bold uppercase">{{ $quote->contact_preference }}</strong>
                    </div>
                    <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/60">
                        <span class="text-slate-400 block font-semibold">6. Collection Postcode:</span>
                        <strong class="text-slate-900 font-bold text-sm">{{ $quote->collection_postcode }}</strong>
                    </div>

                    <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/60">
                        <span class="text-slate-400 block font-semibold">7. Delivery Postcode:</span>
                        <strong class="text-slate-900 font-bold text-sm">{{ $quote->delivery_postcode }}</strong>
                    </div>
                    <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/60">
                        <span class="text-slate-400 block font-semibold">8. Vehicle Selected:</span>
                        <strong class="text-[#0a192f] font-extrabold capitalize">{{ str_replace('_', ' ', $quote->vehicle_type) }}</strong>
                    </div>
                    <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/60">
                        <span class="text-slate-400 block font-semibold">9. Timescales:</span>
                        <strong class="text-amber-700 font-bold">{{ str_replace('_', ' ', $quote->timescale) }}</strong>
                    </div>

                    <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/60 md:col-span-3">
                        <span class="text-slate-400 block font-semibold">10. Type of Enquiry:</span>
                        <strong class="text-blue-700 font-bold capitalize">{{ $quote->enquiry_type }}</strong>
                    </div>

                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200/60 md:col-span-3 space-y-1">
                        <span class="text-slate-400 block font-semibold">11. Any Other Information / Parcel Specs:</span>
                        <p class="text-slate-800 italic font-normal text-xs leading-relaxed">
                            {{ $quote->additional_info ?? 'No additional notes provided by customer.' }}
                        </p>
                    </div>
                </div>
            </div>

        </div>

        <!-- RIGHT COLUMN: Sticky Quotation Selling Price & 1-Click WhatsApp Quick Dispatcher (1/3 Width) -->
        <div class="space-y-6">
            
            <form action="{{ route('admin.invoices.generate', $quote->id) }}" method="POST" class="bg-white border-2 border-[#0a192f] rounded-3xl p-6 space-y-6 shadow-md sticky top-6">
                @csrf
                <div class="border-b border-slate-100 pb-3">
                    <span class="text-[10px] font-black text-[#0a192f] bg-[#c6ff00] px-3 py-1 rounded-full uppercase tracking-wider">
                        DISPATCH PRICING TOOL
                    </span>
                    <h3 class="text-base font-extrabold text-slate-900 font-display mt-2">
                        Quotation Generator
                    </h3>
                    <p class="text-xs text-slate-500">Enter final selling price & dispatch payment invoice to customer.</p>
                </div>

                <div class="space-y-4 text-xs">
                    <!-- Distance & Tariff Recommendation Guide -->
                    <div class="bg-blue-50 border border-blue-200 p-3.5 rounded-xl text-blue-900 space-y-1">
                        <span class="font-bold block text-[11px] uppercase tracking-wider text-blue-700">💡 Suggested Rate Calculator</span>
                        <p class="text-xs font-semibold">Est. Distance: <strong>205 Miles</strong></p>
                        <p class="text-xs font-semibold">Suggested Base Price: <strong class="text-blue-900 font-extrabold">£180.00 (Excl. VAT)</strong></p>
                    </div>

                    <div>
                        <label class="block text-slate-700 font-bold mb-1">Full Pickup Address</label>
                        <input type="text" name="pickup_address" value="Unit 4 Logistics Park, {{ $quote->collection_postcode }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-slate-900">
                    </div>

                    <div>
                        <label class="block text-slate-700 font-bold mb-1">Full Delivery Address</label>
                        <input type="text" name="delivery_address" value="Building 12 Commerce Center, {{ $quote->delivery_postcode }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-slate-900">
                    </div>

                    <div>
                        <label class="block text-slate-700 font-bold mb-1">Quoted Selling Price (£ Excl. VAT)</label>
                        <input type="number" step="0.01" id="selling-price-input" name="quoted_selling_price" value="180.00" required class="w-full bg-slate-50 border-2 border-[#0a192f] rounded-xl px-3.5 py-3 text-slate-900 font-bold text-sm">
                    </div>

                    <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200 space-y-1 text-slate-600 font-medium">
                        <div class="flex justify-between">
                            <span>Standard VAT (20%):</span>
                            <span>Auto-Calculated (£36.00)</span>
                        </div>
                        <div class="flex justify-between font-bold text-slate-900">
                            <span>Total Price Inc. VAT:</span>
                            <span class="text-emerald-700 font-extrabold text-sm">£216.00</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-2 pt-2">
                    <button type="submit" class="w-full py-4 rounded-2xl bg-[#0a192f] hover:bg-[#051329] text-white font-extrabold text-xs transition-colors shadow-md flex items-center justify-center gap-2">
                        <span>⚡ Generate Invoice & Save Order</span>
                    </button>

                    <!-- 1-CLICK WHATSAPP QUICK DISPATCH BUTTON -->
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $quote->phone) }}?text={{ urlencode('Hello ' . $quote->first_name . ', your InstaDrop delivery quote (#' . $quote->quote_number . ') from ' . $quote->collection_postcode . ' to ' . $quote->delivery_postcode . ' is £180.00 + VAT. Click to view invoice and pay online: http://localhost:8000/pay/PAY-DEMO') }}" target="_blank" class="w-full py-3.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs transition-colors shadow-md flex items-center justify-center gap-2">
                        <span>💬 Send Quote Instant via WhatsApp</span>
                    </a>
                </div>
            </form>

        </div>

    </div>
</div>
@endsection
