<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Jobs\SendNewOrderWhatsAppAlert;

class SendOrderWhatsAppNotification
{
    public function handle(OrderCreated $event): void
    {
        SendNewOrderWhatsAppAlert::dispatch($event->order->id);
    }
}
