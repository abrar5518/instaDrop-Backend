<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    protected $table = 'system_settings';

    protected $fillable = [
        'business_name', 'hotline_phone', 'public_phone', 'support_email',
        'admin_notification_email', 'office_address', 'business_address',
        'admin_whatsapp_number', 'opening_hours', 'currency_code', 'vat_rate',
        'mail_host', 'mail_port', 'mail_username', 'mail_password',
        'mail_encryption', 'mail_from_address', 'paypal_client_id',
        'paypal_secret', 'paypal_mode', 'whatsapp_api_token',
        'stripe_public_key', 'stripe_secret_key', 'header_logo_path',
        'footer_logo_path', 'favicon_path', 'facebook_url', 'x_url',
        'instagram_url', 'tiktok_url', 'youtube_url', 'linkedin_url',
        'facebook_enabled', 'x_enabled', 'instagram_enabled', 'tiktok_enabled',
        'youtube_enabled', 'linkedin_enabled', 'google_tag_manager_id',
        'google_tag_manager_enabled', 'google_analytics_id',
        'google_analytics_enabled', 'meta_pixel_id', 'meta_pixel_enabled',
    ];

    protected function casts(): array
    {
        return [
            'vat_rate' => 'decimal:2',
            'facebook_enabled' => 'boolean',
            'x_enabled' => 'boolean',
            'instagram_enabled' => 'boolean',
            'tiktok_enabled' => 'boolean',
            'youtube_enabled' => 'boolean',
            'linkedin_enabled' => 'boolean',
            'google_tag_manager_enabled' => 'boolean',
            'google_analytics_enabled' => 'boolean',
            'meta_pixel_enabled' => 'boolean',
        ];
    }
}
