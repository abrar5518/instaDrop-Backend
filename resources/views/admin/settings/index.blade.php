@extends('admin.layout')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900 font-display">System Settings & Website Contact Management</h1>
        <p class="text-xs text-slate-500">Edit business name, phone hotline, email, office address, and WhatsApp number. All updates immediately reflect across the frontend website.</p>
    </div>

    <form action="{{ route('admin.settings.update') }}" method="POST" class="bg-white border border-slate-200/80 rounded-3xl p-6 sm:p-8 space-y-6 text-xs shadow-xs">
        @csrf

        <div class="border-b border-slate-100 pb-4">
            <h3 class="text-sm font-extrabold text-slate-900 font-display">Website Public Contact Information</h3>
            <p class="text-xs text-slate-500">These details are dynamically displayed on Header, Footer, Contact page, and Call buttons.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div class="space-y-1.5">
                <label class="block font-bold text-slate-700">Business / Company Name</label>
                <input type="text" name="business_name" value="{{ $setting->business_name }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-900 font-medium">
            </div>

            <div class="space-y-1.5">
                <label class="block font-bold text-slate-700">Hotline Phone Number (Header & Footer)</label>
                <input type="text" name="hotline_phone" value="{{ $setting->hotline_phone }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-900 font-bold">
            </div>

            <div class="space-y-1.5">
                <label class="block font-bold text-slate-700">Support Email Address</label>
                <input type="email" name="support_email" value="{{ $setting->support_email }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-900 font-medium">
            </div>

            <div class="space-y-1.5">
                <label class="block font-bold text-slate-700">Admin Business WhatsApp Hotline</label>
                <input type="text" name="admin_whatsapp_number" value="{{ $setting->admin_whatsapp_number }}" required class="w-full bg-slate-50 border border-[#0a192f] rounded-xl px-4 py-3 text-[#0a192f] font-extrabold">
            </div>

            <div class="sm:col-span-2 space-y-1.5">
                <label class="block font-bold text-slate-700">Full Head Office Address</label>
                <input type="text" name="office_address" value="{{ $setting->office_address }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-900 font-medium">
            </div>

            <div class="space-y-1.5">
                <label class="block font-bold text-slate-700">Opening Hours / Availability</label>
                <input type="text" name="opening_hours" value="{{ $setting->opening_hours }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-900 font-medium">
            </div>

            <div class="space-y-1.5">
                <label class="block font-bold text-slate-700">VAT Rate (%)</label>
                <input type="number" step="0.01" name="vat_rate" value="{{ $setting->vat_rate }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-900 font-medium">
            </div>

            <input type="hidden" name="currency_code" value="{{ $setting->currency_code }}">
        </div>

        <div class="border-t border-slate-100 pt-6 space-y-4">
            <h3 class="text-sm font-extrabold text-slate-900 font-display">3rd-Party API Integrations (Optional)</h3>

            <div class="space-y-1.5">
                <label class="block font-bold text-slate-600">WhatsApp Gateway API Token</label>
                <input type="text" name="whatsapp_api_token" value="{{ $setting->whatsapp_api_token }}" placeholder="Paste Twilio / UltraMsg / Meta API Token here when ready" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-900">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block font-bold text-slate-600">Stripe Public Key</label>
                    <input type="text" name="stripe_public_key" value="{{ $setting->stripe_public_key }}" placeholder="pk_test_..." class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-900">
                </div>

                <div class="space-y-1.5">
                    <label class="block font-bold text-slate-600">Stripe Secret Key</label>
                    <input type="password" name="stripe_secret_key" value="{{ $setting->stripe_secret_key }}" placeholder="sk_test_..." class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-900">
                </div>
            </div>
        </div>

        <div class="pt-4">
            <button type="submit" class="w-full py-4 rounded-2xl bg-[#0a192f] hover:bg-[#051329] text-white font-extrabold text-xs transition-all shadow-md">
                ⚡ Save Business Settings & Update Website Contact Details
            </button>
        </div>
    </form>
</div>
@endsection
