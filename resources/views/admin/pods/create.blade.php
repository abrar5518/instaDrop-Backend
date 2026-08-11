@extends('admin.layout')

@section('content')
<div class="max-w-2xl mx-auto space-y-8">
    <div>
        <h1 class="text-2xl font-extrabold text-white">Upload Proof of Delivery (POD)</h1>
        <p class="text-xs text-slate-400">Order Tracking #: <strong class="text-[#c6ff00]">{{ $order->tracking_number }}</strong></p>
    </div>

    <form action="{{ route('admin.pods.store', $order->id) }}" method="POST" enctype="multipart/form-data" class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-6 text-xs">
        @csrf

        <div class="space-y-2">
            <label class="block font-bold text-white">Recipient Name (Person who signed/received)</label>
            <input type="text" name="recipient_name" placeholder="e.g. Sarah Mitchell" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-white">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="space-y-2">
                <label class="block font-bold text-white">Recipient Signature Image / PDF</label>
                <input type="file" name="signature_file" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2 text-slate-300">
            </div>

            <div class="space-y-2">
                <label class="block font-bold text-white">Delivery Photo Proof (Optional)</label>
                <input type="file" name="photo_file" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2 text-slate-300">
            </div>
        </div>

        <div class="space-y-2">
            <label class="block font-bold text-white">Delivery Timestamp</label>
            <input type="datetime-local" name="delivered_at" value="{{ date('Y-m-d\TH:i') }}" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-white">
        </div>

        <div class="pt-4">
            <button type="submit" class="w-full py-4 rounded-2xl bg-[#c6ff00] hover:bg-[#b2e600] text-[#0a192f] font-extrabold text-sm transition-all shadow-lg shadow-[#c6ff00]/20">
                ⚡ Save POD & Auto-Dispatch Notification to Customer
            </button>
        </div>
    </form>
</div>
@endsection
