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
        'admin_whatsapp_number',
        'admin_notification_email',
        'currency_code',
        'vat_rate',
        'whatsapp_api_token',
        'stripe_public_key',
        'stripe_secret_key',
    ];
}
