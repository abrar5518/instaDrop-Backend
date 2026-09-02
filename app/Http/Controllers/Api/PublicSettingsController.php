<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;

class PublicSettingsController extends Controller
{
    public function show()
    {
        $setting = SystemSetting::first();

        return response()->json([
            'business_name' => $setting?->business_name ?? 'InstaDrop Courier Services Ltd',
            'phone' => $setting?->public_phone ?? '0800 123 4455',
            'whatsapp' => $setting?->admin_whatsapp_number ?? '+448001234455',
            'email' => $setting?->admin_notification_email ?? 'dispatch@instadrop.co.uk',
            'address' => $setting?->business_address ?? 'Central Logistics Park, M25 Hub Highway, London UK',
        ]);
    }
}
