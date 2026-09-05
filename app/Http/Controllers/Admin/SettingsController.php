<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $setting = SystemSetting::firstOrCreate([], [
            'business_name' => 'InstaDrop Courier Services Ltd',
            'public_phone' => '0800 123 4455',
            'admin_whatsapp_number' => '+448001234455',
            'admin_notification_email' => 'dispatch@instadrop.uk',
            'business_address' => 'Central Logistics Park, M25 Hub Highway, London UK',
            'currency_code' => 'GBP',
            'vat_rate' => 20.00,
        ]);

        return view('admin.settings.index', compact('setting'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'business_name'            => 'required|string|max:100',
            'public_phone'             => 'required|string|max:50',
            'admin_whatsapp_number'    => 'required|string|max:50',
            'admin_notification_email' => 'required|email|max:100',
            'business_address'         => 'required|string|max:255',
            'currency_code'            => 'required|string|max:10',
            'vat_rate'                 => 'required|numeric|min:0|max:100',
            'whatsapp_api_token'       => 'nullable|string',
            'stripe_public_key'        => 'nullable|string',
            'stripe_secret_key'        => 'nullable|string',
            'facebook_url'             => 'nullable|url|max:255',
            'x_url'                    => 'nullable|url|max:255',
            'instagram_url'            => 'nullable|url|max:255',
            'tiktok_url'               => 'nullable|url|max:255',
            'youtube_url'              => 'nullable|url|max:255',
            'linkedin_url'             => 'nullable|url|max:255',
            'google_tag_manager_id'    => ['nullable','regex:/^GTM-[A-Z0-9]+$/'],
            'google_analytics_id'      => ['nullable','regex:/^G-[A-Z0-9]+$/'],
            'meta_pixel_id'            => ['nullable','regex:/^[0-9]+$/'],
        ]);

        foreach (['facebook','x','instagram','tiktok','youtube','linkedin','google_tag_manager','google_analytics','meta_pixel'] as $name) {
            $validated[$name.'_enabled'] = $request->boolean($name.'_enabled');
        }

        $setting = SystemSetting::first();
        if ($setting) {
            $setting->update($validated);
        } else {
            SystemSetting::create($validated);
        }

        return redirect()->back()->with('success', 'System settings updated successfully.');
    }
}
