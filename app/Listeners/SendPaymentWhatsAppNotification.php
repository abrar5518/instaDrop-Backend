<?php

namespace App\Listeners;

use App\Events\PaymentReceived;
use App\Jobs\SendPaymentReceivedWhatsAppAlert;

class SendPaymentWhatsAppNotification
{
    public function handle(PaymentReceived $event): void
    {
        SendPaymentReceivedWhatsAppAlert::dispatch($event->order->id);
    }
}
