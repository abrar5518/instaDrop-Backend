<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        $setting = SystemSetting::firstOrCreate([], $this->defaults());

        return view('admin.settings.index', compact('setting'));
    }

    public function update(Request $request)
    {
        $request->merge([
            'google_tag_manager_id' => $this->normaliseTrackingId($request->input('google_tag_manager_id')),
            'google_analytics_id' => $this->normaliseTrackingId($request->input('google_analytics_id')),
            'meta_pixel_id' => $this->normaliseTrackingId($request->input('meta_pixel_id'), false),
        ]);

        $validated = $request->validate([
            'business_name' => 'required|string|max:100',
            'hotline_phone' => 'required|string|max:50',
            'support_email' => 'required|email|max:100',
            'office_address' => 'required|string|max:255',
            'admin_whatsapp_number' => 'required|string|max:50',
            'opening_hours' => 'required|string|max:100',
            'currency_code' => 'required|string|max:10',
            'vat_rate' => 'required|numeric|min:0|max:100',
            'mail_host' => 'nullable|string|max:100',
            'mail_port' => 'nullable|string|max:10',
            'mail_username' => 'nullable|string|max:100',
            'mail_password' => 'nullable|string',
            'mail_encryption' => 'nullable|string|in:tls,ssl',
            'mail_from_address' => 'nullable|email|max:100',
            'paypal_client_id' => 'nullable|string',
            'paypal_secret' => 'nullable|string',
            'paypal_mode' => 'nullable|string|in:sandbox,live',
            'whatsapp_api_token' => 'nullable|string',
            'stripe_public_key' => 'nullable|string',
            'stripe_secret_key' => 'nullable|string',
            'facebook_url' => 'nullable|required_if:facebook_enabled,1|url:http,https|max:255',
            'x_url' => 'nullable|required_if:x_enabled,1|url:http,https|max:255',
            'instagram_url' => 'nullable|required_if:instagram_enabled,1|url:http,https|max:255',
            'tiktok_url' => 'nullable|required_if:tiktok_enabled,1|url:http,https|max:255',
            'youtube_url' => 'nullable|required_if:youtube_enabled,1|url:http,https|max:255',
            'linkedin_url' => 'nullable|required_if:linkedin_enabled,1|url:http,https|max:255',
            'google_tag_manager_id' => ['nullable', 'required_if:google_tag_manager_enabled,1', 'regex:/^GTM-[A-Z0-9]+$/'],
            'google_analytics_id' => ['nullable', 'required_if:google_analytics_enabled,1', 'regex:/^G-[A-Z0-9]+$/'],
            'meta_pixel_id' => ['nullable', 'required_if:meta_pixel_enabled,1', 'regex:/^[0-9]+$/'],
            'header_logo' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'footer_logo' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'favicon' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp,ico', 'max:1024'],
        ]);

        $setting = SystemSetting::firstOrCreate([], $this->defaults());

        foreach (['facebook', 'x', 'instagram', 'tiktok', 'youtube', 'linkedin', 'google_tag_manager', 'google_analytics', 'meta_pixel'] as $name) {
            $validated[$name.'_enabled'] = $request->boolean($name.'_enabled');
        }

        $replacedBrandAssets = [];

        foreach (['header_logo' => 'header_logo_path', 'footer_logo' => 'footer_logo_path', 'favicon' => 'favicon_path'] as $input => $column) {
            unset($validated[$input]);

            if (! $request->hasFile($input)) {
                continue;
            }

            $newPath = $request->file($input)->store('branding', 'public');
            $oldPath = $setting->{$column};
            $validated[$column] = $newPath;

            if ($oldPath && str_starts_with($oldPath, 'branding/')) {
                $replacedBrandAssets[] = $oldPath;
            }
        }

        foreach (['mail_password', 'paypal_secret', 'whatsapp_api_token', 'stripe_secret_key'] as $secret) {
            if (blank($validated[$secret] ?? null)) {
                unset($validated[$secret]);
            }
        }

        foreach (['mail_host', 'mail_port', 'mail_username', 'mail_encryption', 'mail_from_address', 'paypal_mode'] as $field) {
            if (blank($validated[$field] ?? null)) {
                unset($validated[$field]);
            }
        }

        $validated['public_phone'] = $validated['hotline_phone'];
        $validated['admin_notification_email'] = $validated['support_email'];
        $validated['business_address'] = $validated['office_address'];
        $setting->update($validated);

        foreach ($replacedBrandAssets as $oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        return back()->with('success', 'System settings updated successfully.');
    }

    private function normaliseTrackingId(mixed $value, bool $uppercase = true): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return $uppercase ? strtoupper($value) : $value;
    }

    private function defaults(): array
    {
        return [
            'business_name' => 'InstaDrop Courier Services',
            'hotline_phone' => '0800 123 4455',
            'public_phone' => '0800 123 4455',
            'support_email' => 'dispatch@instadrop.co.uk',
            'admin_notification_email' => 'dispatch@instadrop.co.uk',
            'office_address' => "100 Pall Mall, St. James's, London, SW1Y 5NQ",
            'business_address' => "100 Pall Mall, St. James's, London, SW1Y 5NQ",
            'admin_whatsapp_number' => '+448001234455',
            'opening_hours' => '24/7 Dispatch Desk - 365 Days a Year',
            'currency_code' => 'GBP',
            'vat_rate' => 20.00,
            'mail_host' => 'smtp.hostinger.com',
            'mail_port' => '587',
            'mail_encryption' => 'tls',
            'paypal_mode' => 'sandbox',
        ];
    }
}
