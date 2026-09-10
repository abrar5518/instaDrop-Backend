<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->string('public_phone', 50)->default('0800 123 4455')->after('business_name');
            $table->string('business_address', 255)->default('Central Logistics Park, M25 Hub Highway, London UK')->after('admin_notification_email');
        });
    }

    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropColumn(['public_phone', 'business_address']);
        });
    }
};
