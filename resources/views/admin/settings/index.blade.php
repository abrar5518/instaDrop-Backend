@extends('admin.layout')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900 font-display">System Settings & Payment Integration</h1>
        <p class="text-xs text-slate-500">Manage public contact info, SMTP email server credentials, PayPal keys, and WhatsApp hotline.</p>
    </div>

    <form action="{{ route('admin.settings.update') }}" method="POST" class="bg-white border border-slate-200/80 rounded-3xl p-6 sm:p-8 space-y-8 text-xs shadow-xs">
        @csrf

        <!-- 1. PUBLIC CONTACT DETAILS -->
        <div class="space-y-4">
            <div class="border-b border-slate-100 pb-3">
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
                    <label class="block font-bold text-slate-700">Support / Admin Notification Email</label>
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
        </div>

        <!-- 2. PAYPAL REST API CREDENTIALS CONFIGURATION -->
        <div class="border-t border-slate-100 pt-6 space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <span class="text-[10px] font-black text-amber-900 bg-amber-100 px-3 py-1 rounded-full uppercase tracking-wider">OFFICIAL PAYPAL REST INTEGRATION</span>
                <h3 class="text-sm font-extrabold text-slate-900 font-display mt-2">PayPal REST API Credentials</h3>
                <p class="text-xs text-slate-500">Configure Client ID & Secret Key provided by client for online PayPal checkout.</p>
            </div>

            <div class="grid grid-cols-1 gap-4">
                <div class="space-y-1.5">
                    <label class="block font-bold text-slate-700">PayPal Client ID</label>
                    <input type="text" name="paypal_client_id" value="{{ $setting->paypal_client_id ?? 'BAA4lZysh2qOP6owh18e_QDB4cOAMTtaCqu56DkwQATYEdnWeElOcIZ435-LpKJiYQhP2HhZiokONbViXA' }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-900 font-mono text-xs">
                </div>

                <div class="space-y-1.5">
                    <label class="block font-bold text-slate-700">PayPal Client Secret</label>
                    <input type="password" name="paypal_secret" value="{{ $setting->paypal_secret ?? 'EK2K4d8PjmiYJdSWsdt3Y7LMC5YCLnoaYXCnSUHuTatxppMgLo7YyPQ-WqMAkCQw1_zDQJhTsed6KgE7' }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-900 font-mono text-xs">
                </div>

                <div class="space-y-1.5 sm:w-1/2">
                    <label class="block font-bold text-slate-700">PayPal Gateway Mode</label>
                    <select name="paypal_mode" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-900 font-bold">
                        <option value="sandbox" {{ ($setting->paypal_mode ?? 'sandbox') === 'sandbox' ? 'selected' : '' }}>Sandbox (Test Payments)</option>
                        <option value="live" {{ ($setting->paypal_mode ?? '') === 'live' ? 'selected' : '' }}>Live (Real Production Payments)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- 3. SMTP EMAIL SERVER CONFIGURATION -->
        <div class="border-t border-slate-100 pt-6 space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <span class="text-[10px] font-black text-blue-900 bg-blue-100 px-3 py-1 rounded-full uppercase tracking-wider">LIVE EMAIL SERVER CONFIG</span>
                <h3 class="text-sm font-extrabold text-slate-900 font-display mt-2">SMTP Mail Server Credentials</h3>
                <p class="text-xs text-slate-500">Configure Hostinger, cPanel Webmail, Gmail, or Mailgun credentials to send real inquiry emails to Admin and receipts to customers.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block font-bold text-slate-700">SMTP Mail Host</label>
                    <input type="text" name="mail_host" value="{{ $setting->mail_host ?? 'smtp.hostinger.com' }}" placeholder="e.g. smtp.hostinger.com or mail.instadrop.co.uk" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-900 font-medium">
                </div>

                <div class="space-y-1.5">
                    <label class="block font-bold text-slate-700">SMTP Mail Port</label>
                    <input type="text" name="mail_port" value="{{ $setting->mail_port ?? '587' }}" placeholder="587 or 465" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-900 font-medium">
                </div>

                <div class="space-y-1.5">
                    <label class="block font-bold text-slate-700">SMTP Username / Email</label>
                    <input type="text" name="mail_username" value="{{ $setting->mail_username ?? 'dispatch@instadrop.co.uk' }}" placeholder="dispatch@instadrop.co.uk" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-900 font-medium">
                </div>

                <div class="space-y-1.5">
                    <label class="block font-bold text-slate-700">SMTP Password</label>
                    <input type="password" name="mail_password" value="{{ $setting->mail_password }}" placeholder="••••••••••••" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-900 font-medium">
                </div>

                <div class="space-y-1.5">
                    <label class="block font-bold text-slate-700">Mail Encryption</label>
                    <select name="mail_encryption" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-900 font-medium">
                        <option value="tls" {{ ($setting->mail_encryption ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS (Port 587)</option>
                        <option value="ssl" {{ ($setting->mail_encryption ?? '') === 'ssl' ? 'selected' : '' }}>SSL (Port 465)</option>
                    </select>
                </div>

                <div class="space-y-1.5">
                    <label class="block font-bold text-slate-700">Sender "From" Email Address</label>
                    <input type="email" name="mail_from_address" value="{{ $setting->mail_from_address ?? 'dispatch@instadrop.co.uk' }}" placeholder="dispatch@instadrop.co.uk" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-900 font-medium">
                </div>
            </div>
        </div>

        <div class="pt-4">
            <button type="submit" class="w-full py-4 rounded-2xl bg-[#0a192f] hover:bg-[#051329] text-white font-extrabold text-xs transition-all shadow-md">
                ⚡ Save All Settings & Apply Live PayPal & Email Credentials
            </button>
        </div>
    </form>
</div>
@endsection
