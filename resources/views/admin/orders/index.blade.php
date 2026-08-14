@extends('admin.layout')

@section('content')
<div class="space-y-6 w-full">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 font-display">Orders & Delivery Lifecycle Control</h1>
            <p class="text-xs text-slate-500">Manage active deliveries, rider assignments, payment verification, and POD uploads.</p>
        </div>
        <span class="text-xs font-bold text-emerald-800 bg-emerald-50 border border-emerald-200 px-3.5 py-1.5 rounded-full">
            ● {{ $orders->count() ?? 1 }} Active Deliveries
        </span>
    </div>

    <!-- INTERACTIVE STATUS FILTER TABS -->
    <div class="flex items-center gap-2 border-b border-slate-200 text-xs font-bold pb-2">
        <button class="px-4 py-2 rounded-xl bg-[#0a192f] text-white shadow-xs">
            All Orders ({{ $orders->count() ?? 1 }})
        </button>
        <button class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors">
            Pending Payment
        </button>
        <button class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors">
            In Transit
        </button>
        <button class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors">
            Completed PODs
        </button>
    </div>

    <!-- Orders Overview Table -->
    <div class="bg-white border border-slate-200/80 rounded-3xl p-6 sm:p-8 shadow-xs space-y-4">
        <div class="overflow-x-auto text-xs">
            <table class="w-full text-left">
                <thead class="bg-slate-50 text-slate-500 text-[11px] uppercase font-bold border-b border-slate-200">
                    <tr>
                        <th class="p-3.5">Order / Ref #</th>
                        <th class="p-3.5">Customer & Route</th>
                        <th class="p-3.5">Vehicle</th>
                        <th class="p-3.5">Selling vs Rider Cost</th>
                        <th class="p-3.5">Payment Verification</th>
                        <th class="p-3.5">Delivery Status & Progress</th>
                        <th class="p-3.5">POD Certificate</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($orders as $order)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-3.5">
                            <span class="font-extrabold text-[#0a192f] block">{{ $order->order_number }}</span>
                            <span class="text-[10px] text-slate-400 font-medium">Ref: {{ $order->quoteRequest->quote_number ?? 'Q-88492' }}</span>
                        </td>
                        <td class="p-3.5">
                            <strong class="text-slate-900 block font-bold">{{ $order->customer_name }}</strong>
                            <span class="text-slate-500 text-[11px] block">{{ $order->pickup_address }} ➔ {{ $order->delivery_address }}</span>
                        </td>
                        <td class="p-3.5 font-bold text-[#0a192f]">
                            {{ str_replace('_', ' ', $order->vehicle_type) }}
                        </td>
                        <td class="p-3.5">
                            <strong class="text-slate-900 block">Selling: £{{ number_format($order->total_amount, 2) }}</strong>
                            <span class="text-slate-500 block text-[11px]">Rider: £{{ number_format($order->driver_cost ?? 120.00, 2) }}</span>
                            <span class="text-emerald-700 font-black text-[11px] block">Profit: £{{ number_format($order->net_profit ?? 60.00, 2) }}</span>
                        </td>

                        <!-- Step 12: Payment Verification & Manual Override Switcher -->
                        <td class="p-3.5 space-y-1">
                            @if($order->payment_status === 'paid')
                                <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase bg-emerald-100 text-emerald-800">
                                    ✓ PAID ONLINE
                                </span>
                            @else
                                <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase bg-amber-100 text-amber-800">
                                    ⏳ UNPAID
                                </span>
                            @endif

                            <!-- Manual Override Action Form -->
                            <form action="{{ route('admin.orders.payment-status', $order->id) }}" method="POST" class="pt-1">
                                @csrf
                                <select name="payment_status" onchange="this.form.submit()" class="bg-slate-50 border border-slate-300 rounded-md px-2 py-1 text-[10px] text-slate-800 font-semibold">
                                    <option value="unpaid" {{ $order->payment_status === 'unpaid' ? 'selected' : '' }}>Mark as Unpaid</option>
                                    <option value="paid" {{ $order->payment_status === 'paid' ? 'selected' : '' }}>Mark as Paid (Cash/BACS)</option>
                                </select>
                            </form>
                        </td>

                        <!-- Delivery Status & Visual Progress Bar -->
                        <td class="p-3.5 space-y-2">
                            <form action="{{ route('admin.orders.status', $order->id) }}" method="POST">
                                @csrf
                                <select name="status" onchange="this.form.submit()" class="bg-slate-50 border border-slate-300 rounded-lg px-2 py-1 text-xs text-slate-800 font-extrabold">
                                    <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending Payment</option>
                                    <option value="driver_assigned" {{ $order->status === 'driver_assigned' ? 'selected' : '' }}>Driver Assigned</option>
                                    <option value="dispatched" {{ $order->status === 'dispatched' ? 'selected' : '' }}>Dispatched</option>
                                    <option value="collected" {{ $order->status === 'collected' ? 'selected' : '' }}>Collected</option>
                                    <option value="in_transit" {{ $order->status === 'in_transit' ? 'selected' : '' }}>In Transit</option>
                                    <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                                </select>
                            </form>

                            <!-- Visual Delivery Stepper Progress Bar -->
                            <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                                <div class="bg-[#0066ff] h-full rounded-full transition-all" style="width: {{ $order->status === 'delivered' ? '100%' : ($order->status === 'in_transit' ? '70%' : ($order->status === 'collected' ? '40%' : '20%')) }};"></div>
                            </div>
                        </td>

                        <!-- Step 17: POD Document Uploader -->
                        <td class="p-3.5">
                            @if($order->pod)
                                <span class="text-xs font-bold text-emerald-700 block">✓ POD Uploaded</span>
                            @else
                                <form action="{{ route('admin.pod.store', $order->id) }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-1">
                                    @csrf
                                    <input type="text" name="recipient_name" placeholder="Recipient Name" required class="px-2 py-1 bg-slate-50 border border-slate-200 rounded-md text-[10px]">
                                    <button type="submit" class="px-3 py-1 bg-[#0a192f] text-white rounded-md font-bold text-[10px] hover:bg-[#051329]">
                                        + Upload POD
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-6 text-center text-slate-400 text-xs">No orders recorded yet. Generate a quotation from quote requests to create an order.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
