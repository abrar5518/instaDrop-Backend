<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('services')->orderBy('id')->pluck('slug')->each(function (string $slug): void {
            DB::table('service_section_items')
                ->where('link_url', '/'.$slug)
                ->update(['link_url' => '/services/'.$slug]);
        });
    }

    public function down(): void
    {
        DB::table('services')->orderBy('id')->pluck('slug')->each(function (string $slug): void {
            DB::table('service_section_items')
                ->where('link_url', '/services/'.$slug)
                ->update(['link_url' => '/'.$slug]);
        });
    }
};
