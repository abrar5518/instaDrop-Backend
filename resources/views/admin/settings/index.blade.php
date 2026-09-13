@extends('admin.layout')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900 font-display">System Settings & Website Configuration</h1>
        <p class="text-xs text-slate-500">Manage public details, branding, social profiles, analytics, payments, email, and WhatsApp.</p>
    </div>

    @if ($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-xs text-red-800" role="alert">
            <p class="font-extrabold">Please correct the following settings:</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="bg-white border border-slate-200/80 rounded-3xl p-6 sm:p-8 space-y-8 text-xs shadow-xs">
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

        <!-- 2. WEBSITE BRANDING -->
        <div class="border-t border-slate-100 pt-6 space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <h3 class="text-sm font-extrabold text-slate-900 font-display">Website Branding</h3>
                <p class="text-xs text-slate-500">Upload PNG, JPG or WebP images. Header and footer logos can be different. Maximum logo size 2 MB; favicon 1 MB.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                @foreach(['header_logo' => ['Header Logo', 'header_logo_path'], 'footer_logo' => ['Footer Logo', 'footer_logo_path'], 'favicon' => ['Browser Favicon', 'favicon_path']] as $input => [$label, $column])
                    <div class="rounded-2xl border border-slate-200 p-4 space-y-3">
                        <label for="{{ $input }}" class="block font-bold text-slate-700">{{ $label }}</label>
                        @if ($setting->{$column})
                            <div class="h-20 rounded-xl bg-slate-100 p-3 flex items-center justify-center">
                                <img src="{{ Storage::url($setting->{$column}) }}" alt="Current {{ strtolower($label) }}" class="max-h-full max-w-full object-contain">
                            </div>
                        @else
                            <div class="h-20 rounded-xl bg-slate-100 flex items-center justify-center text-center text-slate-400">Default branding active</div>
                        @endif
                        <input id="{{ $input }}" type="file" name="{{ $input }}" accept="{{ $input === 'favicon' ? 'image/png,image/jpeg,image/webp,image/x-icon,.ico' : 'image/png,image/jpeg,image/webp' }}" class="block w-full text-[11px] file:mr-2 file:rounded-lg file:border-0 file:bg-slate-900 file:px-3 file:py-2 file:text-white">
                    </div>
                @endforeach
            </div>
        </div>

        <!-- 3. PUBLISHED SOCIAL PROFILES -->
        <div class="border-t border-slate-100 pt-6 space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <h3 class="text-sm font-extrabold text-slate-900 font-display">Published Social Profiles</h3>
                <p class="text-xs text-slate-500">Only enabled profiles with a complete URL are displayed beneath the website footer contact details.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach(['facebook' => 'Facebook', 'x' => 'X / Twitter', 'instagram' => 'Instagram', 'tiktok' => 'TikTok', 'youtube' => 'YouTube', 'linkedin' => 'LinkedIn'] as $name => $label)
                    <div class="space-y-2">
                        <div class="flex items-center justify-between gap-3">
                            <label for="{{ $name }}_url" class="font-bold text-slate-700">{{ $label }}</label>
                            <label class="flex items-center gap-2 font-bold text-slate-700">
                                <input type="hidden" name="{{ $name }}_enabled" value="0">
                                <input type="checkbox" name="{{ $name }}_enabled" value="1" @checked(old($name.'_enabled', $setting->{$name.'_enabled'}))>
                                Show
                            </label>
                        </div>
                        <input id="{{ $name }}_url" type="url" name="{{ $name }}_url" value="{{ old($name.'_url', $setting->{$name.'_url'}) }}" placeholder="https://" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-900">
                    </div>
                @endforeach
            </div>
        </div>

        <!-- 4. ANALYTICS AND ADVERTISING -->
        <div class="border-t border-slate-100 pt-6 space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <h3 class="text-sm font-extrabold text-slate-900 font-display">Analytics & Advertising</h3>
                <p class="text-xs text-slate-500">Enable an integration only after entering its valid ID. Enabled integrations are published to the website automatically.</p>
            </div>

            @foreach(['google_tag_manager' => ['Google Tag Manager', 'GTM-XXXXXXX'], 'google_analytics' => ['Google Analytics 4', 'G-XXXXXXXXXX'], 'meta_pixel' => ['Meta Pixel', 'Numeric Pixel ID']] as $name => [$label, $placeholder])
                <div class="rounded-2xl border border-slate-200 p-4">
                    <div class="mb-2 flex items-center justify-between gap-3">
                        <label for="{{ $name }}_id" class="font-extrabold text-slate-700">{{ $label }}</label>
                        <label class="flex items-center gap-2 font-bold text-slate-700">
                            <input type="hidden" name="{{ $name }}_enabled" value="0">
                            <input type="checkbox" name="{{ $name }}_enabled" value="1" @checked(old($name.'_enabled', $setting->{$name.'_enabled'}))>
                            Enabled
                        </label>
                    </div>
                    <input id="{{ $name }}_id" type="text" name="{{ $name }}_id" value="{{ old($name.'_id', $setting->{$name.'_id'}) }}" placeholder="{{ $placeholder }}" autocomplete="off" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 font-mono">
                </div>
            @endforeach
        </div>

        <!-- 5. PAYPAL REST API CREDENTIALS CONFIGURATION -->
        <div class="border-t border-slate-100 pt-6 space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <span class="text-[10px] font-black text-amber-900 bg-amber-100 px-3 py-1 rounded-full uppercase tracking-wider">OFFICIAL PAYPAL REST INTEGRATION</span>
                <h3 class="text-sm font-extrabold text-slate-900 font-display mt-2">PayPal REST API Credentials</h3>
                <p class="text-xs text-slate-500">Configure Client ID & Secret Key provided by client for online PayPal checkout.</p>
            </div>

            <div class="grid grid-cols-1 gap-4">
                <div class="space-y-1.5">
                    <label class="block font-bold text-slate-700">PayPal Client ID</label>
                    <input type="text" name="paypal_client_id" value="{{ $setting->paypal_client_id }}" autocomplete="off" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-900 font-mono text-xs">
                </div>

                <div class="space-y-1.5">
                    <label class="block font-bold text-slate-700">PayPal Client Secret</label>
                    <input type="password" name="paypal_secret" value="" autocomplete="new-password" placeholder="{{ $setting->paypal_secret ? 'Leave blank to keep the current secret' : 'Enter PayPal secret' }}" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-900 font-mono text-xs">
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

        <!-- 6. SMTP EMAIL SERVER CONFIGURATION -->
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
                    <input type="password" name="mail_password" value="" autocomplete="new-password" placeholder="{{ $setting->mail_password ? 'Leave blank to keep the current password' : 'Enter SMTP password' }}" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-900 font-medium">
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
                Save System Settings
            </button>
        </div>
    </form>
</div>
@endsection
