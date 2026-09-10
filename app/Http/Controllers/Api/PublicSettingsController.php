<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Storage;

class PublicSettingsController extends Controller
{
    public function show()
    {
        $setting = SystemSetting::first();

        return response()->json([
            'business_name' => $setting?->business_name ?? 'InstaDrop Courier Services Ltd',
            'phone' => $setting?->public_phone ?? '0800 123 4455',
            'whatsapp' => $setting?->admin_whatsapp_number ?? '+448001234455',
            'email' => $setting?->admin_notification_email ?? 'dispatch@instadrop.uk',
            'address' => $setting?->business_address ?? 'Central Logistics Park, M25 Hub Highway, London UK',
            'branding' => [
                'header_logo_url' => $setting?->header_logo_path ? url(Storage::url($setting->header_logo_path)) : null,
                'footer_logo_url' => $setting?->footer_logo_path ? url(Storage::url($setting->footer_logo_path)) : null,
                'favicon_url' => $setting?->favicon_path ? url(Storage::url($setting->favicon_path)) : null,
            ],
            'social_links' => [
                'facebook' => $setting?->facebook_enabled ? $setting->facebook_url : null,
                'x' => $setting?->x_enabled ? $setting->x_url : null,
                'instagram' => $setting?->instagram_enabled ? $setting->instagram_url : null,
                'tiktok' => $setting?->tiktok_enabled ? $setting->tiktok_url : null,
                'youtube' => $setting?->youtube_enabled ? $setting->youtube_url : null,
                'linkedin' => $setting?->linkedin_enabled ? $setting->linkedin_url : null,
            ],
            'tracking' => [
                'gtm_id' => $setting?->google_tag_manager_enabled ? $setting->google_tag_manager_id : null,
                'ga_id' => $setting?->google_analytics_enabled ? $setting->google_analytics_id : null,
                'meta_pixel_id' => $setting?->meta_pixel_enabled ? $setting->meta_pixel_id : null,
            ],
        ]);
    }
}
