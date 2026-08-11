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
            $table->string('admin_whatsapp_number', 50)->default('+448001234455');
            $table->string('admin_notification_email', 100)->default('dispatch@instadrop.co.uk');
            $table->string('currency_code', 10)->default('GBP');
            $table->decimal('vat_rate', 5, 2)->default(20.00);
            $table->string('whatsapp_api_token', 255)->nullable();
            $table->string('stripe_public_key', 255)->nullable();
            $table->string('stripe_secret_key', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
