<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    protected $table = 'system_settings';

    protected $fillable = [
        'business_name',
        'public_phone',
        'admin_whatsapp_number',
        'admin_notification_email',
        'business_address',
        'facebook_url', 'x_url', 'instagram_url', 'tiktok_url', 'youtube_url', 'linkedin_url',
        'currency_code',
        'vat_rate',
        'whatsapp_api_token',
        'stripe_public_key',
        'stripe_secret_key',
    ];
}
