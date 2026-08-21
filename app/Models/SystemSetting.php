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
        'hotline_phone',
        'support_email',
        'office_address',
        'admin_whatsapp_number',
        'opening_hours',
        'currency_code',
        'vat_rate',
        'mail_host',
        'mail_port',
        'mail_username',
        'mail_password',
        'mail_encryption',
        'mail_from_address',
        'whatsapp_api_token',
        'stripe_public_key',
        'stripe_secret_key',
    ];
}
