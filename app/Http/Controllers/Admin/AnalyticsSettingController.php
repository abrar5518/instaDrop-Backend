<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsSetting;
use Illuminate\Http\Request;

class AnalyticsSettingController extends Controller
{
    /**
     * Show analytics and tracking pixel settings page or data.
     */
    public function index()
    {
        $settings = AnalyticsSetting::getSettings();

        return response()->json([
            'success' => true,
            'settings' => $settings,
        ]);
    }

    /**
     * Update analytics, pixel, SEO and contact settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'meta_pixel_id' => 'nullable|string|max:100',
            'gtm_container_id' => 'nullable|string|max:100',
            'ga4_measurement_id' => 'nullable|string|max:100',
            'clarity_project_id' => 'nullable|string|max:100',
            'google_search_console_code' => 'nullable|string|max:1000',
            'is_enabled' => 'boolean',
            'site_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|string|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'contact_address' => 'nullable|string|max:1000',
            'facebook_url' => 'nullable|string|url|max:255',
            'instagram_url' => 'nullable|string|url|max:255',
        ]);

        $settings = AnalyticsSetting::getSettings();
        $settings->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Analytics, Tracking Pixels & SEO settings updated successfully.',
            'settings' => $settings,
        ]);
    }
}
