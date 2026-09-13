<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteRequest extends Model
{
    use HasFactory;

    protected $table = 'quote_requests';

    protected $fillable = [
        'quote_number',
        'first_name',
        'last_name',
        'email',
        'phone',
        'contact_preference',
        'collection_postcode',
        'delivery_postcode',
        'vehicle_type',
        'timescale',
        'enquiry_type',
        'collection_date',
        'collection_time',
        'additional_info',
        'status',
        'admin_notes',
    ];

    public function getCollectionScheduleAttribute(): string
    {
        if (!$this->collection_date || !$this->collection_time) return 'Not specified';
        return \Carbon\CarbonImmutable::parse($this->collection_date)->format('d M Y').' at '.substr($this->collection_time, 0, 5).' (UK time)';
    }

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function order()
    {
        return $this->hasOne(Order::class, 'quote_request_id');
    }
}
