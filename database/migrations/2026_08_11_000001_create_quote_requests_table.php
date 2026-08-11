<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_requests', function (Blueprint $table) {
            $table->id();
            $table->string('quote_number', 30)->unique();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email', 100);
            $table->string('phone', 50);
            $table->enum('contact_preference', ['whatsapp', 'email', 'phone_call'])->default('email');
            $table->string('collection_postcode', 20);
            $table->string('delivery_postcode', 20);
            $table->string('vehicle_type', 50);
            $table->string('timescale', 50)->default('asap_60min');
            $table->enum('enquiry_type', ['business', 'personal'])->default('business');
            $table->text('additional_info')->nullable();
            $table->enum('status', ['pending', 'quoted', 'converted', 'cancelled'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_requests');
    }
};
