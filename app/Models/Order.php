<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'quote_request_id',
        'tracking_number',
        'customer_name',
        'customer_email',
        'customer_phone',
        'preferred_contact_method',
        'pickup_address',
        'delivery_address',
        'vehicle_type',
        'carrier_name',
        'quoted_selling_price',
        'status',
    ];

    public function quoteRequest()
    {
        return $this->belongsTo(QuoteRequest::class, 'quote_request_id');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'order_id');
    }

    public function pod()
    {
        return $this->hasOne(Pod::class, 'order_id');
    }
}
