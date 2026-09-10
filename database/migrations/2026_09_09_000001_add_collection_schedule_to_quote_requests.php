<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('quote_requests', function (Blueprint $table) {
            $table->date('collection_date')->nullable();
            $table->time('collection_time')->nullable();
            // Preserve historical enquiry values, but new requests no longer collect one.
            $table->enum('enquiry_type', ['business', 'personal'])->nullable()->default(null)->change();
        });
    }
    public function down(): void
    {
        Schema::table('quote_requests', function (Blueprint $table) {
            $table->dropColumn(['collection_date', 'collection_time']);
        });
    }
};
