<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Return public business contact settings for Next.js frontend
     */
    public function show()
    {
        $setting = SystemSetting::firstOrCreate([], [
            'business_name'         => 'InstaDrop Courier Services Ltd',
            'hotline_phone'         => '0800 123 4455',
            'support_email'         => 'dispatch@instadrop.co.uk',
            'office_address'        => '100 Pall Mall, St. James\'s, London, SW1Y 5NQ',
            'admin_whatsapp_number' => '+448001234455',
            'opening_hours'         => '24/7 Dispatch Desk • 365 Days a Year',
            'currency_code'         => 'GBP',
            'vat_rate'              => 20.00,
        ]);

        return response()->json([
            'success' => true,
            'settings' => [
                'business_name'         => $setting->business_name,
                'hotline_phone'         => $setting->hotline_phone,
                'support_email'         => $setting->support_email,
                'office_address'        => $setting->office_address,
                'admin_whatsapp_number' => $setting->admin_whatsapp_number,
                'opening_hours'         => $setting->opening_hours,
                'currency_code'         => $setting->currency_code,
                'vat_rate'              => $setting->vat_rate,
            ],
        ]);
    }
}
