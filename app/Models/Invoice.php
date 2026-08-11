<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $table = 'invoices';

    protected $fillable = [
        'order_id',
        'invoice_number',
        'payment_token',
        'subtotal',
        'vat_amount',
        'total_amount',
        'status',
        'payment_method',
        'payment_transaction_id',
        'paid_at',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
