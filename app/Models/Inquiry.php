<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    use HasFactory;

    protected $fillable = [
        'inquiry_type', 'name', 'email', 'phone', 'company_name',
        'company_registration', 'monthly_deliveries', 'subject', 'message', 'status',
    ];
}
