<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_directory_settings', function (Blueprint $table) {
            $table->id();
            $table->string('hero_badge')->nullable();
            $table->string('hero_title')->nullable();
            $table->text('hero_description')->nullable();
            $table->string('cta_label')->nullable();
            $table->string('cta_url')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_image_path')->nullable();
            $table->boolean('noindex')->default(false);
            $table->boolean('nofollow')->default(false);
            $table->timestamps();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('navigation_title')->nullable();
            $table->string('slug')->unique();
            $table->string('icon', 50)->default('truck');
            $table->text('summary')->nullable();
            $table->string('card_badge')->nullable();
            $table->string('card_subtitle')->nullable();
            $table->text('card_description')->nullable();
            $table->json('card_features')->nullable();
            $table->json('card_details')->nullable();
            $table->string('hero_eyebrow')->nullable();
            $table->string('hero_title')->nullable();
            $table->text('hero_description')->nullable();
            $table->string('hero_primary_label')->nullable();
            $table->string('hero_primary_url')->nullable();
            $table->string('hero_secondary_label')->nullable();
            $table->string('hero_secondary_url')->nullable();
            $table->json('hero_points')->nullable();
            $table->string('hero_image_path')->nullable();
            $table->string('hero_image_alt')->nullable();
            $table->string('route_kicker')->nullable();
            $table->string('route_counter')->nullable();
            $table->string('route_title')->nullable();
            $table->string('collection_label')->nullable();
            $table->string('collection_detail')->nullable();
            $table->string('delivery_label')->nullable();
            $table->string('delivery_detail')->nullable();
            $table->string('route_footer')->nullable();
            $table->string('route_status')->nullable();
            $table->string('notice_title')->nullable();
            $table->text('notice_body')->nullable();
            $table->string('sidebar_title')->nullable();
            $table->text('sidebar_body')->nullable();
            $table->string('sidebar_cta_label')->nullable();
            $table->string('sidebar_cta_url')->nullable();
            $table->text('sidebar_helpful_details')->nullable();
            $table->string('bottom_cta_title')->nullable();
            $table->text('bottom_cta_body')->nullable();
            $table->string('bottom_cta_label')->nullable();
            $table->string('bottom_cta_url')->nullable();
            $table->string('status', 20)->default('draft')->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_image_path')->nullable();
            $table->boolean('noindex')->default(false);
            $table->boolean('nofollow')->default(false);
            $table->timestamps();
        });

        Schema::create('service_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('section_type', 30);
            $table->string('anchor_id')->nullable();
            $table->string('label')->nullable();
            $table->string('heading')->nullable();
            $table->text('intro')->nullable();
            $table->longText('body')->nullable();
            $table->string('callout_heading')->nullable();
            $table->text('callout_body')->nullable();
            $table->string('image_path')->nullable();
            $table->string('image_alt')->nullable();
            $table->boolean('show_in_sidebar')->default(false);
            $table->boolean('is_enabled')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['service_id', 'sort_order']);
        });

        Schema::create('service_section_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_section_id')->constrained()->cascadeOnDelete();
            $table->string('badge')->nullable();
            $table->string('title')->nullable();
            $table->text('body')->nullable();
            $table->string('link_label')->nullable();
            $table->string('link_url')->nullable();
            $table->string('image_path')->nullable();
            $table->string('image_alt')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['service_section_id', 'sort_order']);
        });

        Schema::create('coverage_pages', function (Blueprint $table) {
            $table->id();
            $table->string('hero_badge')->nullable();
            $table->string('hero_title');
            $table->text('hero_description')->nullable();
            $table->string('regions_eyebrow')->nullable();
            $table->string('regions_heading')->nullable();
            $table->string('bottom_cta_title')->nullable();
            $table->text('bottom_cta_body')->nullable();
            $table->string('bottom_cta_label')->nullable();
            $table->string('bottom_cta_url')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_image_path')->nullable();
            $table->boolean('noindex')->default(false);
            $table->boolean('nofollow')->default(false);
            $table->string('status', 20)->default('published');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('coverage_regions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coverage_page_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('timing')->nullable();
            $table->text('hubs')->nullable();
            $table->text('postcodes')->nullable();
            $table->string('icon', 50)->default('building');
            $table->boolean('is_enabled')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['coverage_page_id', 'sort_order']);
        });

        Schema::create('coverage_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coverage_page_id')->constrained()->cascadeOnDelete();
            $table->string('eyebrow')->nullable();
            $table->string('heading')->nullable();
            $table->text('body')->nullable();
            $table->string('cta_label')->nullable();
            $table->string('cta_url')->nullable();
            $table->string('theme', 20)->default('dark');
            $table->boolean('is_enabled')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['coverage_page_id', 'sort_order']);
        });

        $this->migrateLegacyContent();
    }

    private function migrateLegacyContent(): void
    {
        $now = now();
        $directory = DB::table('content_pages')->where('page_type', 'services_index')->first();
        DB::table('service_directory_settings')->insert([
            'id' => 1,
            'hero_badge' => $directory?->hero_badge,
            'hero_title' => $directory?->hero_title ?? 'Dedicated Courier Services. Tailored For Every Cargo.',
            'hero_description' => $directory?->hero_description,
            'cta_label' => $directory?->primary_cta_label ?? 'Get Speedy Quote Now',
            'cta_url' => $directory?->primary_cta_url ?? '/instant-quote',
            'meta_title' => $directory?->meta_title,
            'meta_description' => $directory?->meta_description,
            'meta_keywords' => $directory?->meta_keywords,
            'og_title' => $directory?->og_title,
            'og_description' => $directory?->og_description,
            'og_image_path' => $directory?->og_image_path,
            'noindex' => $directory?->noindex ?? false,
            'nofollow' => $directory?->nofollow ?? false,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        foreach (DB::table('content_pages')->where('page_type', 'service')->orderBy('sort_order')->get() as $legacy) {
            $serviceId = DB::table('services')->insertGetId([
                'title' => $legacy->title,
                'navigation_title' => $legacy->navigation_title,
                'slug' => $legacy->slug,
                'icon' => $legacy->icon,
                'summary' => $legacy->summary,
                'card_badge' => $legacy->card_badge,
                'card_subtitle' => $legacy->card_subtitle,
                'card_description' => $legacy->card_description,
                'card_features' => $legacy->card_features,
                'card_details' => $legacy->card_details,
                'hero_eyebrow' => $legacy->hero_badge,
                'hero_title' => $legacy->hero_title,
                'hero_description' => $legacy->hero_description,
                'hero_primary_label' => $legacy->primary_cta_label,
                'hero_primary_url' => $legacy->primary_cta_url,
                'hero_secondary_label' => 'Call the 24/7 desk',
                'hero_secondary_url' => 'tel:+447852502775',
                'hero_points' => json_encode(['Dedicated vehicle', 'UK-wide journeys', 'Proof of delivery']),
                'hero_image_path' => $legacy->hero_image_path,
                'hero_image_alt' => $legacy->hero_image_alt,
                'route_kicker' => 'THE DIRECT ROUTE',
                'route_counter' => '01 / 02',
                'route_title' => "One collection.\nOne destination.\nClear updates.",
                'collection_label' => 'Collection',
                'collection_detail' => 'Time confirmed by dispatch',
                'delivery_label' => 'Delivery',
                'delivery_detail' => 'Recipient confirmation and POD',
                'route_footer' => 'YOUR CONSIGNMENT, ITS OWN JOURNEY',
                'route_status' => 'DIRECT',
                'notice_title' => 'Need a precise collection window?',
                'notice_body' => 'Share the pickup and delivery postcodes, parcel details and deadline when requesting a quote. Dispatch will confirm what is possible.',
                'sidebar_title' => 'Have an urgent delivery?',
                'sidebar_body' => 'Send the pickup and delivery details for a tailored quote, or speak to the dispatch desk.',
                'sidebar_cta_label' => 'Request a quote',
                'sidebar_cta_url' => '/instant-quote',
                'sidebar_helpful_details' => 'Collection and delivery postcodes · number of items · dimensions and weight · required handover time · site access and loading instructions.',
                'bottom_cta_title' => 'Tell us what needs to move.',
                'bottom_cta_body' => 'Share the journey and deadline. Dispatch will confirm the collection options and provide a quote matched to your consignment.',
                'bottom_cta_label' => 'Get my delivery quote',
                'bottom_cta_url' => '/instant-quote',
                'status' => $legacy->status,
                'sort_order' => $legacy->sort_order,
                'published_at' => $legacy->published_at,
                'meta_title' => $legacy->meta_title,
                'meta_description' => $legacy->meta_description,
                'meta_keywords' => $legacy->meta_keywords,
                'og_title' => $legacy->og_title,
                'og_description' => $legacy->og_description,
                'og_image_path' => $legacy->og_image_path,
                'noindex' => $legacy->noindex,
                'nofollow' => $legacy->nofollow,
                'created_at' => $legacy->created_at,
                'updated_at' => $legacy->updated_at,
            ]);

            $legacySections = DB::table('content_sections')->where('content_page_id', $legacy->id)->orderBy('sort_order')->get();
            foreach ($legacySections as $legacySection) {
                $type = match ($legacySection->section_type) {
                    'steps' => 'steps', 'faq' => 'faq', 'rich_text' => 'content', 'cta' => 'callout',
                    default => 'tiles',
                };
                $sectionId = DB::table('service_sections')->insertGetId([
                    'service_id' => $serviceId,
                    'section_type' => $type,
                    'anchor_id' => 'section-'.($legacySection->sort_order + 1),
                    'label' => $legacySection->eyebrow,
                    'heading' => $legacySection->heading,
                    'intro' => $legacySection->body,
                    'body' => null,
                    'callout_heading' => null,
                    'callout_body' => null,
                    'image_path' => $legacySection->image_path,
                    'image_alt' => $legacySection->image_alt,
                    'show_in_sidebar' => true,
                    'is_enabled' => $legacySection->is_enabled,
                    'sort_order' => $legacySection->sort_order,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                foreach (DB::table('content_section_items')->where('content_section_id', $legacySection->id)->orderBy('sort_order')->get() as $item) {
                    DB::table('service_section_items')->insert([
                        'service_section_id' => $sectionId,
                        'badge' => $item->badge,
                        'title' => $item->title,
                        'body' => $item->description ?: $item->subtitle,
                        'link_label' => $item->link_label,
                        'link_url' => $item->link_url,
                        'image_path' => $item->image_path,
                        'image_alt' => $item->image_alt,
                        'sort_order' => $item->sort_order,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }

        $this->seedSameDayTemplateContent($now);

        $coverage = DB::table('content_pages')->where('page_type', 'coverage')->first();
        $coverageId = DB::table('coverage_pages')->insertGetId([
            'hero_badge' => $coverage?->hero_badge,
            'hero_title' => $coverage?->hero_title ?? 'UK Nationwide Dispatch Hubs',
            'hero_description' => $coverage?->hero_description,
            'regions_eyebrow' => 'REGIONAL DISPATCH CENTRES',
            'regions_heading' => 'Select Your Local Pickup Region',
            'bottom_cta_title' => 'Need a courier outside these regions?',
            'bottom_cta_body' => 'Share the collection and delivery postcodes and dispatch will confirm current availability.',
            'bottom_cta_label' => 'Get a quote',
            'bottom_cta_url' => '/instant-quote',
            'meta_title' => $coverage?->meta_title,
            'meta_description' => $coverage?->meta_description,
            'meta_keywords' => $coverage?->meta_keywords,
            'og_title' => $coverage?->og_title,
            'og_description' => $coverage?->og_description,
            'og_image_path' => $coverage?->og_image_path,
            'noindex' => $coverage?->noindex ?? false,
            'nofollow' => $coverage?->nofollow ?? false,
            'status' => $coverage?->status ?? 'published',
            'published_at' => $coverage?->published_at ?? $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        if ($coverage) {
            foreach (DB::table('content_sections')->where('content_page_id', $coverage->id)->orderBy('sort_order')->get() as $legacySection) {
                $items = DB::table('content_section_items')->where('content_section_id', $legacySection->id)->orderBy('sort_order')->get();
                if ($legacySection->section_type === 'region_grid') {
                    foreach ($items as $item) {
                        DB::table('coverage_regions')->insert([
                            'coverage_page_id' => $coverageId,
                            'title' => $item->title,
                            'timing' => $item->badge,
                            'hubs' => $item->description,
                            'postcodes' => $item->value,
                            'icon' => $item->icon ?: 'building',
                            'is_enabled' => true,
                            'sort_order' => $item->sort_order,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }
                } else {
                    DB::table('coverage_sections')->insert([
                        'coverage_page_id' => $coverageId,
                        'eyebrow' => $legacySection->eyebrow,
                        'heading' => $legacySection->heading,
                        'body' => $legacySection->body,
                        'cta_label' => $legacySection->cta_label,
                        'cta_url' => $legacySection->cta_url,
                        'theme' => $legacySection->theme,
                        'is_enabled' => $legacySection->is_enabled,
                        'sort_order' => $legacySection->sort_order,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
    }

    private function seedSameDayTemplateContent($now): void
    {
        $service = DB::table('services')->where('slug', 'same-day-delivery')->first();
        if (! $service) return;
        DB::table('services')->where('id', $service->id)->update([
            'hero_eyebrow' => 'Courier services / Same-day delivery',
            'hero_title' => 'Same-day courier delivery, arranged around your deadline.',
            'hero_description' => 'When a parcel cannot wait for a standard delivery network, book a dedicated vehicle to collect it and travel directly towards its destination. InstaDrop arranges urgent courier journeys across the UK, with collection timing confirmed by dispatch and delivery updates along the way.',
            'hero_primary_label' => 'Get a same-day quote',
            'notice_title' => 'Need a precise collection window?',
            'notice_body' => 'Share the pickup and delivery postcodes, parcel details and deadline when requesting a quote. Dispatch will confirm what is possible.',
        ]);
        DB::table('service_sections')->where('service_id', $service->id)->delete();
        $add = function (array $section, array $items = []) use ($service, $now): void {
            $sectionId = DB::table('service_sections')->insertGetId(array_merge([
                'service_id' => $service->id, 'section_type' => 'content', 'anchor_id' => null,
                'label' => null, 'heading' => null, 'intro' => null, 'body' => null,
                'callout_heading' => null, 'callout_body' => null, 'image_path' => null,
                'image_alt' => null, 'show_in_sidebar' => true, 'is_enabled' => true,
                'sort_order' => 0, 'created_at' => $now, 'updated_at' => $now,
            ], $section));
            foreach ($items as $index => $item) DB::table('service_section_items')->insert(array_merge([
                'service_section_id' => $sectionId, 'badge' => null, 'title' => null, 'body' => null,
                'link_label' => null, 'link_url' => null, 'image_path' => null, 'image_alt' => null,
                'sort_order' => $index, 'created_at' => $now, 'updated_at' => $now,
            ], $item));
        };
        $add([
            'anchor_id' => 'overview', 'label' => 'Service overview', 'heading' => 'Urgent delivery without the usual stops',
            'intro' => 'A same-day courier is useful when the delivery date, handover or handling requirements matter more than the convenience of a standard parcel round. Rather than passing through a sorting network, your consignment travels in a vehicle allocated to your booking.',
            'body' => "InstaDrop's service is designed for businesses and individuals moving time-sensitive items between UK locations. Give dispatch the collection address, destination, size and weight, along with any deadline or access instructions.\n\nSame-day delivery is subject to the route, booking time, vehicle availability and the practical requirements of your consignment.",
            'callout_heading' => 'What “dedicated” means here',
            'callout_body' => 'The vehicle is reserved for your consignment, so the journey is planned around your collection and delivery addresses.',
            'sort_order' => 0,
        ]);
        $add(['section_type' => 'steps', 'anchor_id' => 'how', 'heading' => 'How a same-day booking works', 'intro' => 'Urgency should not mean guessing what happens next.', 'sort_order' => 1], [
            ['badge' => '01 / REQUEST', 'title' => 'Tell us the journey', 'body' => 'Provide both postcodes, the item description, dimensions, weight and preferred times.'],
            ['badge' => '02 / CONFIRM', 'title' => 'Agree the booking', 'body' => 'Dispatch checks the route and vehicle, then confirms price and realistic collection timing.'],
            ['badge' => '03 / DELIVER', 'title' => 'Follow the progress', 'body' => 'The consignment travels to its destination and proof of delivery is shared where available.'],
        ]);
        $add(['section_type' => 'tiles', 'anchor_id' => 'items', 'heading' => 'What can you send by same-day courier?', 'intro' => 'The service can be arranged for eligible consignments from small parcels to commercial loads.', 'sort_order' => 2], [
            ['title' => 'Business-critical items', 'body' => 'Replacement components, samples, exhibition materials, signed paperwork and equipment needed for an appointment.'],
            ['title' => 'Larger consignments', 'body' => 'For pallets, machinery or heavy goods, explain loading access and equipment at both locations.'],
        ]);
        $add(['anchor_id' => 'vehicle', 'heading' => 'Choosing the right vehicle', 'body' => "Supply the total number of pieces, individual dimensions and approximate weight. State whether anything is stackable and how it will be loaded and unloaded.\n\nDispatch can then match the job to the available fleet.", 'sort_order' => 3]);
        $add(['anchor_id' => 'coverage', 'heading' => 'Where can InstaDrop collect and deliver?', 'body' => 'InstaDrop arranges same-day courier journeys across the UK. Whether a route can be completed on the same day depends on booking time, distance and available vehicles.', 'sort_order' => 4]);
        $add(['anchor_id' => 'pricing', 'heading' => 'How much does a same-day courier cost?', 'body' => 'A same-day courier quote is based on the actual movement. Distance, timing, vehicle size, loading needs, waiting time and special handling can affect the price.', 'callout_heading' => 'Before requesting a quote', 'callout_body' => 'Have both postcodes, item dimensions, approximate weight, contact details and deadline ready.', 'sort_order' => 5]);
        $add(['section_type' => 'faq', 'anchor_id' => 'questions', 'heading' => 'Frequently asked questions', 'sort_order' => 6], [
            ['title' => 'Can I book a same-day courier at any time?', 'body' => 'The dispatch desk is available 24/7. Collection timing is confirmed after route and vehicle checks.'],
            ['title' => 'How quickly can a driver collect my parcel?', 'body' => 'Timing varies by location, booking time and vehicle availability. Provide the pickup postcode and deadline.'],
            ['title' => 'Will my consignment share a vehicle?', 'body' => 'This service uses a dedicated vehicle booking for your consignment.'],
            ['title' => 'Do I get proof of delivery?', 'body' => 'Delivery-status or POD information is provided where available and agreed.'],
        ]);
        $add(['section_type' => 'related', 'anchor_id' => 'related', 'heading' => 'Explore other courier services', 'intro' => 'Compare specialist handling and delivery patterns before booking.', 'show_in_sidebar' => false, 'sort_order' => 7], [
            ['title' => 'Dedicated vehicle delivery', 'link_label' => 'Dedicated vehicle delivery', 'link_url' => '/dedicated-vehicle-delivery'],
            ['title' => 'Medical courier', 'link_label' => 'Medical courier', 'link_url' => '/medical-courier'],
            ['title' => 'Legal courier', 'link_label' => 'Legal courier', 'link_url' => '/legal-courier'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('coverage_sections');
        Schema::dropIfExists('coverage_regions');
        Schema::dropIfExists('coverage_pages');
        Schema::dropIfExists('service_section_items');
        Schema::dropIfExists('service_sections');
        Schema::dropIfExists('services');
        Schema::dropIfExists('service_directory_settings');
    }
};
