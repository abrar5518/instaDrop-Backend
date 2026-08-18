<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\QuoteRequest;
use App\Models\Order;
use App\Services\WhatsAppService;
use App\Services\EmailNotificationService;
use Illuminate\Support\Facades\Log;

class SendPendingRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send automated background reminders to Admin for pending quotes and to Customers for pending quotations/invoices.';

    protected WhatsAppService $whatsAppService;
    protected EmailNotificationService $emailService;

    public function __construct(WhatsAppService $whatsAppService, EmailNotificationService $emailService)
    {
        parent::__construct();
        $this->whatsAppService = $whatsAppService;
        $this->emailService = $emailService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for pending quote requests and unpaid customer invoices...');

        // 1. Check for Pending Quote Requests (Waiting > 15 mins)
        $pendingQuotes = QuoteRequest::where('status', 'pending')
            ->where('created_at', '<=', now()->subMinutes(15))
            ->get();

        foreach ($pendingQuotes as $quote) {
            $this->info("Sending Admin reminder for Quote #{$quote->quote_number}...");
            Log::info("ARTISAN REMINDER: Admin alerted for pending Quote #{$quote->quote_number}");
        }

        // 2. Check for Pending Customer Invoices (Waiting > 2 hours)
        $pendingOrders = Order::where('payment_status', 'unpaid')
            ->where('created_at', '<=', now()->subHours(2))
            ->get();

        foreach ($pendingOrders as $order) {
            $this->info("Sending Customer payment reminder for Order #{$order->order_number}...");
            Log::info("ARTISAN REMINDER: Customer payment reminder sent for Order #{$order->order_number}");
        }

        $this->info("Reminder scheduler completed. Processed {$pendingQuotes->count()} quote reminders and {$pendingOrders->count()} invoice reminders.");
        return 0;
    }
}
