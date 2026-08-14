<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'quote_request_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'pickup_address',
        'delivery_address',
        'vehicle_type',
        'total_amount',
        'driver_cost',
        'net_profit',
        'status',
        'payment_status',
        'payment_method',
        'driver_name',
        'driver_phone',
        'driver_vehicle_reg',
    ];

    public function quoteRequest()
    {
        return $this->belongsTo(QuoteRequest::class, 'quote_request_id');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function pod()
    {
        return $this->hasOne(Pod::class);
    }
}
