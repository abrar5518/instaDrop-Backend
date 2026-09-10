<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up():void{Schema::table('system_settings',function(Blueprint $table){$table->string('header_logo_path')->nullable();$table->string('footer_logo_path')->nullable();$table->string('favicon_path')->nullable();});} public function down():void{Schema::table('system_settings',function(Blueprint $table){$table->dropColumn(['header_logo_path','footer_logo_path','favicon_path']);});}};
