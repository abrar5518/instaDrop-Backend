<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_request_id')->nullable()->constrained('quote_requests')->onDelete('set null');
            $table->string('tracking_number', 50)->unique();
            $table->string('customer_name', 100);
            $table->string('customer_email', 100);
            $table->string('customer_phone', 50);
            $table->enum('preferred_contact_method', ['whatsapp', 'email', 'phone_call'])->default('whatsapp');
            $table->text('pickup_address');
            $table->text('delivery_address');
            $table->string('vehicle_type', 50);
            $table->string('carrier_name', 100)->nullable();
            $table->decimal('quoted_selling_price', 10, 2);
            $table->enum('status', ['pending_payment', 'paid', 'dispatched', 'collected', 'in_transit', 'delivered', 'cancelled'])->default('pending_payment');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
