<?php

namespace App\Listeners;

use App\Events\InquiryCreated;
use App\Jobs\SendNewInquiryWhatsAppAlert;

class SendInquiryWhatsAppNotification
{
    public function handle(InquiryCreated $event): void
    {
        SendNewInquiryWhatsAppAlert::dispatch($event->quote->id);
    }
}
