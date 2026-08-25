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
            'business_name'         => 'InstaDrop Courier Services Ltd',
            'hotline_phone'         => '0800 123 4455',
            'support_email'         => 'dispatch@instadrop.co.uk',
            'office_address'        => '100 Pall Mall, St. James\'s, London, SW1Y 5NQ',
            'admin_whatsapp_number' => '+448001234455',
            'opening_hours'         => '24/7 Dispatch Desk • 365 Days a Year',
            'currency_code'         => 'GBP',
            'vat_rate'              => 20.00,
            'mail_host'             => 'smtp.hostinger.com',
            'mail_port'             => '587',
            'mail_username'         => 'dispatch@instadrop.co.uk',
            'mail_encryption'       => 'tls',
            'mail_from_address'     => 'dispatch@instadrop.co.uk',
            'paypal_client_id'     => 'BAA4lZysh2qOP6owh18e_QDB4cOAMTtaCqu56DkwQATYEdnWeElOcIZ435-LpKJiYQhP2HhZiokONbViXA',
            'paypal_secret'        => 'EK2K4d8PjmiYJdSWsdt3Y7LMC5YCLnoaYXCnSUHuTatxppMgLo7YyPQ-WqMAkCQw1_zDQJhTsed6KgE7',
            'paypal_mode'          => 'sandbox',
        ]);

        return view('admin.settings.index', compact('setting'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'business_name'            => 'required|string|max:100',
            'hotline_phone'            => 'required|string|max:50',
            'support_email'            => 'required|email|max:100',
            'office_address'           => 'required|string|max:255',
            'admin_whatsapp_number'    => 'required|string|max:50',
            'opening_hours'            => 'required|string|max:100',
            'currency_code'            => 'required|string|max:10',
            'vat_rate'                 => 'required|numeric|min:0|max:100',
            'mail_host'                => 'nullable|string|max:100',
            'mail_port'                => 'nullable|string|max:10',
            'mail_username'            => 'nullable|string|max:100',
            'mail_password'            => 'nullable|string',
            'mail_encryption'          => 'nullable|string|max:10',
            'mail_from_address'        => 'nullable|email|max:100',
            'paypal_client_id'         => 'nullable|string',
            'paypal_secret'            => 'nullable|string',
            'paypal_mode'              => 'nullable|string|max:20',
            'whatsapp_api_token'       => 'nullable|string',
            'stripe_public_key'        => 'nullable|string',
            'stripe_secret_key'        => 'nullable|string',
        ]);

        $setting = SystemSetting::first();
        if ($setting) {
            $setting->update($validated);
        } else {
            SystemSetting::create($validated);
        }

        return redirect()->back()->with('success', 'System business settings, PayPal credentials, and SMTP Mail settings updated successfully!');
    }
}
