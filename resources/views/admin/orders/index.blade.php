@extends('admin.layout')

@section('content')
<div class="space-y-8">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900 font-display">Orders & Delivery Status Control</h1>
        <p class="text-xs text-slate-500">Manage live active deliveries, update status, and upload POD signature/photo</p>
    </div>

    <div class="bg-white border border-slate-200/80 rounded-3xl p-6 space-y-4 shadow-xs">
        <div class="overflow-x-auto text-xs">
            <table class="w-full text-left">
                <thead class="bg-slate-50 text-slate-500 text-[11px] uppercase font-bold border-b border-slate-200">
                    <tr>
                        <th class="p-3.5">Tracking #</th>
                        <th class="p-3.5">Customer Name</th>
                        <th class="p-3.5">Vehicle</th>
                        <th class="p-3.5">Price Paid</th>
                        <th class="p-3.5">Delivery Status</th>
                        <th class="p-3.5">Update Status</th>
                        <th class="p-3.5">POD Document</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($orders as $order)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="p-3.5 font-extrabold text-[#0a192f]">{{ $order->tracking_number }}</td>
                            <td class="p-3.5 font-bold text-slate-900">{{ $order->customer_name }}</td>
                            <td class="p-3.5 capitalize">{{ str_replace('_', ' ', $order->vehicle_type) }}</td>
                            <td class="p-3.5 font-bold text-slate-900">£{{ number_format($order->quoted_selling_price, 2) }}</td>
                            <td class="p-3.5">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase bg-blue-100 text-blue-800">
                                    {{ str_replace('_', ' ', $order->status) }}
                                </span>
                            </td>
                            <td class="p-3.5">
                                <form action="{{ route('admin.orders.status', $order->id) }}" method="POST" class="flex items-center gap-2">
                                    @csrf
                                    <select name="status" class="bg-slate-50 border border-slate-300 rounded-lg px-2 py-1 text-xs text-slate-800 font-semibold">
                                        <option value="dispatched" {{ $order->status === 'dispatched' ? 'selected' : '' }}>Dispatched</option>
                                        <option value="collected" {{ $order->status === 'collected' ? 'selected' : '' }}>Collected</option>
                                        <option value="in_transit" {{ $order->status === 'in_transit' ? 'selected' : '' }}>In Transit</option>
                                        <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                                    </select>
                                    <button type="submit" class="px-3 py-1 bg-[#0a192f] text-white rounded-lg text-xs font-bold hover:bg-[#051329]">
                                        Update
                                    </button>
                                </form>
                            </td>
                            <td class="p-3.5">
                                @if ($order->pod)
                                    <span class="text-emerald-700 font-bold text-xs bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-200">✓ POD Uploaded</span>
                                @else
                                    <a href="{{ route('admin.pods.create', $order->id) }}" class="px-3 py-1 bg-[#0a192f] text-white rounded-lg font-bold text-xs hover:bg-[#051329]">
                                        + Upload POD
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-4 text-center text-slate-400">No active orders found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
