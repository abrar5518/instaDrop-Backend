@extends('admin.layout')

@section('content')
<div class="max-w-3xl mx-auto space-y-8">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900 font-display">System Settings & Hotline Configuration</h1>
        <p class="text-xs text-slate-500">Configure Business WhatsApp Number, Email Notifications, and API Keys</p>
    </div>

    <form action="{{ route('admin.settings.update') }}" method="POST" class="bg-white border border-slate-200/80 rounded-3xl p-6 sm:p-8 space-y-6 text-xs shadow-xs">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="space-y-2">
                <label class="block font-bold text-slate-700">Business Name</label>
                <input type="text" name="business_name" value="{{ $setting->business_name }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-900">
            </div>

            <div class="space-y-2">
                <label class="block font-bold text-slate-700">Public Hotline Number</label>
                <input type="text" name="public_phone" value="{{ $setting->public_phone }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-900">
            </div>

            <div class="space-y-2">
                <label class="block font-bold text-slate-700">Admin Business WhatsApp Number</label>
                <input type="text" name="admin_whatsapp_number" value="{{ $setting->admin_whatsapp_number }}" required class="w-full bg-slate-50 border border-[#0a192f] rounded-xl px-4 py-3 text-[#0a192f] font-bold">
            </div>

            <div class="space-y-2">
                <label class="block font-bold text-slate-700">Admin Notification Email</label>
                <input type="email" name="admin_notification_email" value="{{ $setting->admin_notification_email }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-900">
            </div>

            <div class="space-y-2">
                <label class="block font-bold text-slate-700">Public Business Address</label>
                <input type="text" name="business_address" value="{{ $setting->business_address }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-900">
            </div>

            <div class="space-y-2">
                <label class="block font-bold text-slate-700">VAT Rate (%)</label>
                <input type="number" step="0.01" name="vat_rate" value="{{ $setting->vat_rate }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-900">
            </div>

            <div class="col-span-2 space-y-2">
                <label class="block font-bold text-slate-700">Currency Code</label>
                <input type="text" name="currency_code" value="{{ $setting->currency_code }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-900">
            </div>
        </div>

        <div class="border-t border-slate-100 pt-6 space-y-4">
            <h3 class="text-sm font-extrabold text-slate-900 font-display">Published Social Profiles</h3>
            <p class="text-slate-500">Only completed profile URLs are shown in the website footer.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach(['facebook_url'=>'Facebook','x_url'=>'X / Twitter','instagram_url'=>'Instagram','tiktok_url'=>'TikTok','youtube_url'=>'YouTube','linkedin_url'=>'LinkedIn'] as $field => $label)
                    <div class="space-y-2"><label class="block font-bold text-slate-600">{{ $label }}</label><input type="url" name="{{ $field }}" value="{{ old($field, $setting->$field) }}" placeholder="https://" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-900"></div>
                @endforeach
            </div>
        </div>

        <div class="border-t border-slate-100 pt-6 space-y-4">
            <h3 class="text-sm font-extrabold text-slate-900 font-display">3rd-Party API Tokens (Optional)</h3>

            <div class="space-y-2">
                <label class="block font-bold text-slate-600">WhatsApp Gateway API Token</label>
                <input type="text" name="whatsapp_api_token" value="{{ $setting->whatsapp_api_token }}" placeholder="Paste Twilio / UltraMsg / Meta API Token here when ready" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-900">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label class="block font-bold text-slate-600">Stripe Public Key</label>
                    <input type="text" name="stripe_public_key" value="{{ $setting->stripe_public_key }}" placeholder="pk_test_..." class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-900">
                </div>

                <div class="space-y-2">
                    <label class="block font-bold text-slate-600">Stripe Secret Key</label>
                    <input type="password" name="stripe_secret_key" value="{{ $setting->stripe_secret_key }}" placeholder="sk_test_..." class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-900">
                </div>
            </div>
        </div>

        <div class="pt-4">
            <button type="submit" class="w-full py-4 rounded-2xl bg-[#0a192f] hover:bg-[#051329] text-white font-extrabold text-xs transition-all shadow-md">
                Save System Settings
            </button>
        </div>
    </form>
</div>
@endsection
