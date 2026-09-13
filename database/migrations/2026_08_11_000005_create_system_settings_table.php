<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('business_name', 100)->default('InstaDrop Courier Services');
            $table->string('hotline_phone', 50)->default('0800 123 4455');
            $table->string('support_email', 100)->default('dispatch@instadrop.co.uk');
            $table->string('office_address', 255)->default('100 Pall Mall, St. James, London, SW1Y 5NQ');
            $table->string('admin_whatsapp_number', 50)->default('+448001234455');
            $table->string('opening_hours', 100)->default('24/7 Dispatch Desk • 365 Days a Year');
            $table->string('currency_code', 10)->default('GBP');
            $table->decimal('vat_rate', 5, 2)->default(20.00);
            $table->string('mail_host', 100)->default('smtp.hostinger.com');
            $table->string('mail_port', 10)->default('587');
            $table->string('mail_username', 100)->default('dispatch@instadrop.co.uk');
            $table->text('mail_password')->nullable();
            $table->string('mail_encryption', 10)->default('tls');
            $table->string('mail_from_address', 100)->default('dispatch@instadrop.co.uk');
            $table->text('paypal_client_id')->nullable();
            $table->text('paypal_secret')->nullable();
            $table->string('paypal_mode', 20)->default('sandbox');
            $table->text('whatsapp_api_token')->nullable();
            $table->text('stripe_public_key')->nullable();
            $table->text('stripe_secret_key')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
