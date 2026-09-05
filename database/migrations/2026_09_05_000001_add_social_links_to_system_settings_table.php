<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up():void{Schema::table('system_settings',function(Blueprint $table){foreach(['facebook_url','x_url','instagram_url','tiktok_url','youtube_url','linkedin_url'] as $column){$table->string($column)->nullable();}});} public function down():void{Schema::table('system_settings',function(Blueprint $table){$table->dropColumn(['facebook_url','x_url','instagram_url','tiktok_url','youtube_url','linkedin_url']);});}};
