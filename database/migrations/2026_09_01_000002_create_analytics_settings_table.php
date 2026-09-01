<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('analytics_settings', function (Blueprint $table) {
            $table->id();
            // Tracking & Analytics IDs
            $table->string('meta_pixel_id')->nullable();
            $table->string('gtm_container_id')->nullable();
            $table->string('ga4_measurement_id')->nullable();
            $table->string('clarity_project_id')->nullable();
            $table->text('google_search_console_code')->nullable();
            $table->boolean('is_enabled')->default(true);

            // Organization & Contact Details (for Schema.org, SEO, llms.txt)
            $table->string('site_name')->default('InstaDrop Courier Services');
            $table->string('contact_email')->default('dispatch@instadrop.co.uk');
            $table->string('contact_phone')->default('+44 7852 502775');
            $table->text('contact_address')->default('100 Pall Mall, St. James\'s, London, SW1Y 5NQ');
            $table->string('facebook_url')->nullable();
            $table->string('instagram_url')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_settings');
    }
};
