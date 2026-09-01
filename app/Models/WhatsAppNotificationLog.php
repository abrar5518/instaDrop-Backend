<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsAppNotificationLog extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_notification_logs';

    protected $fillable = [
        'notification_type',
        'source_record_id',
        'recipient',
        'template_name',
        'status',
        'meta_message_id',
        'attempt_count',
        'last_error',
        'sent_at',
    ];

    protected $casts = [
        'attempt_count' => 'integer',
        'sent_at' => 'datetime',
    ];

    public static function isAlreadySent(string $type, string $recordId, string $recipient): bool
    {
        return static::where('notification_type', $type)
            ->where('source_record_id', $recordId)
            ->where('recipient', $recipient)
            ->where('status', 'sent')
            ->exists();
    }
}
