<?php

namespace App\Console\Commands;

use App\Services\WhatsAppCloudApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

class TestWhatsAppAdminAlertCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:test-admin-alert {--type=inquiry : Notification type to test (inquiry, order, payment)} {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test approved WhatsApp notification to the configured admin number';

    /**
     * Execute the console command.
     */
    public function handle(WhatsAppCloudApiService $apiService): int
    {
        if (App::runningUnitTests()) {
            $this->error('Test command refused to run inside automated unit/feature tests environment without mocking.');
            return Command::FAILURE;
        }

        $this->info('Validating WhatsApp Cloud API configuration...');

        $validation = $apiService->validateConfig();

        if (!$validation['valid']) {
            $this->error('WhatsApp configuration is invalid or disabled:');
            foreach ($validation['errors'] as $err) {
                $this->line(" - {$err}");
            }
            return Command::FAILURE;
        }

        $adminNumber = (string) config('whatsapp.admin_number', '447852502775');
        $formattedRecipient = WhatsAppCloudApiService::formatE164($adminNumber);
        $language = (string) config('whatsapp.template_language', 'en');
        $type = strtolower((string) $this->option('type'));

        if ($type === 'payment') {
            $templateName = (string) config('whatsapp.payment_template', 'payment_received_admin_alert');
            $sampleParams = [
                'INSTA-884920',
                'Sarah Mitchell',
                '£180.00',
                'Credit Card',
                'Completed',
            ];
        } elseif ($type === 'order') {
            $templateName = (string) config('whatsapp.order_template', 'new_order_admin_alert');
            $sampleParams = [
                'INSTA-884920',
                'Sarah Mitchell',
                '+44 7123 456789',
                '£180.00',
                'Completed',
            ];
        } else {
            $templateName = (string) config('whatsapp.inquiry_template', 'new_inquiry_admin_alert');
            $sampleParams = [
                'Sarah Mitchell',
                '+44 7123 456789',
                'sarah.m@company.co.uk',
                'Quote #Q-88492 (Business)',
                'Collection: M1 1AE | Delivery: SW1A 1AA | Vehicle: Luton Tail-Lift Van',
            ];
        }

        $this->line("Target Recipient: {$formattedRecipient}");
        $this->line("Notification Type: {$type}");
        $this->line("Template Name:    {$templateName}");
        $this->line("Language Code:    {$language}");

        if (!$this->option('force')) {
            $confirmed = $this->confirm("Are you sure you want to send a test '{$type}' WhatsApp message to {$formattedRecipient}?");
            if (!$confirmed) {
                $this->warn('Test alert cancelled.');
                return Command::SUCCESS;
            }
        }

        $this->info('Sending test WhatsApp message to Meta Cloud API...');

        $result = $apiService->sendTemplate(
            $formattedRecipient,
            $templateName,
            $sampleParams,
            $language
        );

        if ($result['success']) {
            $this->info('SUCCESS: Test WhatsApp message delivered to Meta API!');
            $this->line("Meta Message ID: {$result['message_id']}");
            return Command::SUCCESS;
        }

        $this->error("FAILED: Could not send test WhatsApp message.");
        $this->error("Error Details: {$result['error']}");

        return Command::FAILURE;
    }
}
