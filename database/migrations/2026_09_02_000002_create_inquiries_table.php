<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();
            $table->enum('inquiry_type', ['contact', 'business_account']);
            $table->string('name', 150);
            $table->string('email', 150);
            $table->string('phone', 50);
            $table->string('company_name', 150)->nullable();
            $table->string('company_registration', 100)->nullable();
            $table->string('monthly_deliveries', 100)->nullable();
            $table->string('subject', 150)->nullable();
            $table->text('message')->nullable();
            $table->enum('status', ['new', 'in_progress', 'closed'])->default('new');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquiries');
    }
};
