<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->string('public_phone', 50)->default('0800 123 4455')->after('business_name');
            $table->string('admin_notification_email', 100)->default('dispatch@instadrop.co.uk')->after('support_email');
            $table->string('business_address', 255)->default('Central Logistics Park, M25 Hub Highway, London UK')->after('office_address');
        });
    }

    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropColumn(['public_phone', 'admin_notification_email', 'business_address']);
        });
    }
};
