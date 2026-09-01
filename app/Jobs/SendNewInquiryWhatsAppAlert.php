<?php

namespace App\Jobs;

use App\Models\QuoteRequest;
use App\Models\WhatsAppNotificationLog;
use App\Services\WhatsAppCloudApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendNewInquiryWhatsAppAlert implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public bool $afterCommit = true;
    public int $tries = 3;

    public function backoff(): array
    {
        return [10, 30, 60];
    }

    public function __construct(public int|string $quoteId)
    {
    }

    public static function sanitizeMessage(string $text, int $maxLength = 500): string
    {
        $clean = strip_tags($text);
        $clean = preg_replace('/[\x00-\x1F\x7F]/u', '', $clean) ?? '';
        $clean = preg_replace('/\s+/', ' ', $clean) ?? '';
        $clean = trim($clean);

        if (mb_strlen($clean) > $maxLength) {
            $clean = mb_substr($clean, 0, $maxLength - 3) . '...';
        }

        return $clean ?: 'No message details provided';
    }

    public function handle(WhatsAppCloudApiService $apiService): void
    {
        $quote = QuoteRequest::find($this->quoteId);

        if (!$quote) {
            Log::warning("[SendNewInquiryWhatsAppAlert] Quote ID {$this->quoteId} not found, skipping alert.");
            return;
        }

        $adminNumber = (string) config('whatsapp.admin_number', '447852502775');
        $recipient = WhatsAppCloudApiService::formatE164($adminNumber);
        $templateName = (string) config('whatsapp.inquiry_template', 'new_inquiry_admin_alert');
        $language = (string) config('whatsapp.template_language', 'en');

        // Check duplicate log
        if (WhatsAppNotificationLog::isAlreadySent('inquiry', (string) $quote->id, $recipient)) {
            Log::info("[SendNewInquiryWhatsAppAlert] Quote/Inquiry ID {$quote->id} alert already sent to {$recipient}, skipping.");
            return;
        }

        $logRecord = WhatsAppNotificationLog::firstOrCreate(
            [
                'notification_type' => 'inquiry',
                'source_record_id' => (string) $quote->id,
                'recipient' => $recipient,
            ],
            [
                'template_name' => $templateName,
                'status' => 'pending',
                'attempt_count' => 0,
            ]
        );

        if ($logRecord->status === 'sent') {
            return;
        }

        $logRecord->increment('attempt_count');

        $name = $quote->full_name ?: 'Customer';
        $phone = $quote->phone ?: 'Not provided';
        $email = $quote->email ?: 'Not provided';
        $subject = "Quote #{$quote->quote_number} (" . ($quote->enquiry_type ?: 'General') . ")";
        
        $rawMessage = "Collection: {$quote->collection_postcode} | Delivery: {$quote->delivery_postcode} | Vehicle: {$quote->vehicle_type} | Timescale: {$quote->timescale}";
        if ($quote->additional_info) {
            $rawMessage .= " | Info: {$quote->additional_info}";
        }
        $message = static::sanitizeMessage($rawMessage);

        $parameters = [
            (string) $name,
            (string) $phone,
            (string) $email,
            (string) $subject,
            (string) $message,
        ];

        $result = $apiService->sendTemplate(
            $recipient,
            $templateName,
            $parameters,
            $language
        );

        if ($result['success']) {
            $logRecord->update([
                'status' => 'sent',
                'meta_message_id' => $result['message_id'],
                'sent_at' => now(),
                'last_error' => null,
            ]);
        } else {
            $logRecord->update([
                'status' => 'failed',
                'last_error' => $result['error'],
            ]);

            Log::error("[SendNewInquiryWhatsAppAlert] Failed sending inquiry notification for Quote #{$quote->quote_number}: {$result['error']}");
        }
    }
}
