<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsSetting;
use Illuminate\Http\JsonResponse;

class AnalyticsSettingController extends Controller
{
    /**
     * Get active analytics, tracking pixel IDs, verification codes, contact details and Schema.org structured data.
     */
    public function show(): JsonResponse
    {
        $settings = AnalyticsSetting::getSettings();

        if (!$settings->is_enabled) {
            return response()->json([
                'enabled' => false,
                'meta_pixel_id' => null,
                'gtm_container_id' => null,
                'ga4_measurement_id' => null,
                'clarity_project_id' => null,
                'google_search_console_code' => null,
                'site_name' => $settings->site_name,
                'contact_email' => $settings->contact_email,
                'contact_phone' => $settings->contact_phone,
                'contact_address' => $settings->contact_address,
                'schemas' => [
                    'organization' => $settings->toOrganizationSchema(),
                    'website' => $settings->toWebSiteSchema(),
                ],
            ]);
        }

        return response()->json([
            'enabled' => true,
            'meta_pixel_id' => $settings->meta_pixel_id,
            'gtm_container_id' => $settings->gtm_container_id,
            'ga4_measurement_id' => $settings->ga4_measurement_id,
            'clarity_project_id' => $settings->clarity_project_id,
            'google_search_console_code' => $settings->google_search_console_code,
            'site_name' => $settings->site_name,
            'contact_email' => $settings->contact_email,
            'contact_phone' => $settings->contact_phone,
            'contact_address' => $settings->contact_address,
            'facebook_url' => $settings->facebook_url,
            'instagram_url' => $settings->instagram_url,
            'schemas' => [
                'organization' => $settings->toOrganizationSchema(),
                'website' => $settings->toWebSiteSchema(),
            ],
        ]);
    }
}
