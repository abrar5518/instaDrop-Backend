<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_pages', function (Blueprint $table) {
            $table->id();
            $table->string('page_type', 30)->index();
            $table->string('title');
            $table->string('navigation_title')->nullable();
            $table->string('slug')->unique();
            $table->string('icon', 50)->default('truck');
            $table->text('summary')->nullable();
            $table->string('hero_badge')->nullable();
            $table->string('hero_title')->nullable();
            $table->text('hero_description')->nullable();
            $table->string('hero_image_path')->nullable();
            $table->string('hero_image_alt')->nullable();
            $table->string('primary_cta_label')->nullable();
            $table->string('primary_cta_url')->nullable();
            $table->string('card_badge')->nullable();
            $table->string('card_subtitle')->nullable();
            $table->text('card_description')->nullable();
            $table->json('card_features')->nullable();
            $table->json('card_details')->nullable();
            $table->string('card_image_path')->nullable();
            $table->string('card_image_alt')->nullable();
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

        Schema::create('content_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_page_id')->constrained()->cascadeOnDelete();
            $table->string('section_type', 40);
            $table->string('eyebrow')->nullable();
            $table->string('heading')->nullable();
            $table->text('body')->nullable();
            $table->string('image_path')->nullable();
            $table->string('image_alt')->nullable();
            $table->string('cta_label')->nullable();
            $table->string('cta_url')->nullable();
            $table->string('theme', 20)->default('light');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
            $table->index(['content_page_id', 'sort_order']);
        });

        Schema::create('content_section_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_section_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->string('badge')->nullable();
            $table->string('value')->nullable();
            $table->string('icon', 50)->nullable();
            $table->string('image_path')->nullable();
            $table->string('image_alt')->nullable();
            $table->string('link_label')->nullable();
            $table->string('link_url')->nullable();
            $table->json('metadata')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['content_section_id', 'sort_order']);
        });

        $this->seedExistingContent();
    }

    private function seedExistingContent(): void
    {
        $now = now();
        $page = function (array $data) use ($now): int {
            return DB::table('content_pages')->insertGetId(array_merge([
                'navigation_title' => null, 'icon' => 'truck', 'summary' => null,
                'hero_badge' => null, 'hero_title' => null, 'hero_description' => null,
                'hero_image_path' => null, 'hero_image_alt' => null,
                'primary_cta_label' => 'Get Speedy Quote Now', 'primary_cta_url' => '/instant-quote',
                'card_badge' => null, 'card_subtitle' => null, 'card_description' => null,
                'card_features' => null, 'card_details' => null, 'card_image_path' => null, 'card_image_alt' => null,
                'status' => 'published', 'sort_order' => 0, 'published_at' => $now,
                'meta_title' => null, 'meta_description' => null, 'meta_keywords' => null,
                'og_title' => null, 'og_description' => null, 'og_image_path' => null,
                'noindex' => false, 'nofollow' => false, 'created_at' => $now, 'updated_at' => $now,
            ], $data));
        };
        $section = function (int $pageId, array $data, array $items = []) use ($now): void {
            $sectionId = DB::table('content_sections')->insertGetId(array_merge([
                'content_page_id' => $pageId, 'eyebrow' => null, 'heading' => null, 'body' => null,
                'image_path' => null, 'image_alt' => null, 'cta_label' => null, 'cta_url' => null,
                'theme' => 'light', 'sort_order' => 0, 'is_enabled' => true,
                'created_at' => $now, 'updated_at' => $now,
            ], $data));
            foreach ($items as $index => $item) {
                DB::table('content_section_items')->insert(array_merge([
                    'content_section_id' => $sectionId, 'title' => null, 'subtitle' => null,
                    'description' => null, 'badge' => null, 'value' => null, 'icon' => null,
                    'image_path' => null, 'image_alt' => null, 'link_label' => null, 'link_url' => null,
                    'metadata' => null, 'sort_order' => $index, 'created_at' => $now, 'updated_at' => $now,
                ], $item));
            }
        };

        $servicesIndex = $page([
            'page_type' => 'services_index', 'title' => 'Courier Services Directory', 'slug' => 'services',
            'hero_badge' => 'COMPREHENSIVE UK SAME-DAY SOLUTIONS',
            'hero_title' => 'Dedicated Courier Services. Tailored For Every Cargo.',
            'hero_description' => 'From urgent legal briefs to pallet freight and specialist consignments, InstaDrop arranges door-to-door services across mainland UK, subject to booking requirements and availability.',
            'meta_title' => 'Same-Day Courier Services — Dedicated Van, Pallet & Urgent Logistics',
            'meta_description' => 'Explore InstaDrop same-day dedicated courier services for urgent parcels, pallets, medical items, legal documents and planned deliveries across the UK.',
            'meta_keywords' => 'same day courier services, dedicated van delivery, pallet courier UK, urgent courier',
        ]);
        $section($servicesIndex, ['section_type' => 'comparison', 'eyebrow' => 'THE INSTADROP DIFFERENCE', 'heading' => 'InstaDrop vs Traditional Postal Networks'], [
            ['title' => 'Pickup window', 'subtitle' => 'Confirmed by dispatch', 'description' => 'Broad all-day collection windows'],
            ['title' => 'Sorting depots', 'subtitle' => 'Direct A-to-B service', 'description' => 'Multiple sorting and handling points'],
            ['title' => 'Tracking', 'subtitle' => 'Status and POD where supported', 'description' => 'Barcode scan updates'],
        ]);
        $section($servicesIndex, ['section_type' => 'faq', 'eyebrow' => 'COMMON QUESTIONS', 'heading' => 'Services FAQs', 'sort_order' => 1], [
            ['title' => 'How fast is collection across the UK?', 'description' => 'Dispatch confirms the expected collection window after checking location, route requirements and vehicle availability.'],
            ['title' => 'Are services delivered using dedicated vehicles?', 'description' => 'A dedicated direct vehicle is used where that service is selected and confirmed in the quote.'],
            ['title' => 'How is cover confirmed?', 'description' => 'Cover depends on the declared contents, value and booking terms. Ask dispatch to confirm the applicable limit.'],
        ]);

        $services = [
            ['same-day-delivery', 'Same-Day & Urgent Courier', 'zap', 'Urgent Dispatch', 'Direct door-to-door same-day transport for urgent consignments.', 'Fast collection and direct delivery for urgent documents, parcels, equipment and replacement parts.', ['Urgent documents', 'Parcels and packages', 'Equipment', 'Replacement parts']],
            ['dedicated-vehicle-delivery', 'Dedicated Vehicle Delivery', 'truck', 'Exclusive Vehicle', 'One vehicle reserved exclusively for your consignment.', 'A dedicated vehicle travels directly from collection to destination without depot handling or shared loads.', ['Exclusive-use vehicle', 'Direct point-to-point travel', 'No depot handling', 'No shared loads']],
            ['scheduled-delivery', 'Scheduled Delivery', 'calendar', 'Planned Delivery', 'Book collection and delivery in advance.', 'A courier is arranged for an agreed date or delivery window, subject to vehicle and route availability.', ['Advance bookings', 'Timed collections', 'Agreed delivery dates', 'Planned business movements']],
            ['pallet-delivery', 'Pallet & Heavy Freight Delivery', 'package', 'Heavy Freight', 'Matched vehicles for pallets, machinery and commercial goods.', 'Transport for pallets, machinery and large or heavy goods using a vehicle matched to the declared load.', ['Palletised goods', 'Machinery', 'Large equipment', 'Heavy commercial loads']],
            ['wait-and-return', 'Wait & Return Courier', 'refresh', 'Return Journey', 'Delivery, on-site waiting and return to the original location.', 'The driver delivers, waits while documents are signed or parts exchanged, then returns to the original location.', ['Signed documents', 'Tested equipment', 'Part exchanges', 'Return journeys']],
            ['medical-courier', 'Medical Courier', 'activity', 'Specialist Handling', 'Careful time-critical transport for healthcare consignments.', 'Specialist courier support for medical supplies, samples and equipment, subject to handling and booking requirements.', ['Medical supplies', 'Laboratory materials', 'Specialist equipment', 'Hospital transfers']],
            ['legal-courier', 'Legal & Confidential Courier', 'file-text', 'Confidential Delivery', 'Direct transport for sensitive and deadline-critical documents.', 'Professional hand-to-hand delivery for legal papers, contracts, deeds and confidential documents.', ['Court papers', 'Contracts and deeds', 'Tenders', 'Confidential documents']],
        ];
        foreach ($services as $order => [$slug, $title, $icon, $badge, $subtitle, $description, $features]) {
            $serviceId = $page([
                'page_type' => 'service', 'title' => $title, 'navigation_title' => $title, 'slug' => $slug,
                'icon' => $icon, 'summary' => $subtitle, 'hero_badge' => 'PROFESSIONAL UK COURIER SERVICE',
                'hero_title' => $title, 'hero_description' => $description,
                'card_badge' => $badge, 'card_subtitle' => $subtitle, 'card_description' => $description,
                'card_features' => json_encode($features),
                'card_details' => json_encode([
                    ['label' => 'Collection', 'value' => 'Confirmed by dispatch'],
                    ['label' => 'Journey', 'value' => 'Direct where booked'],
                    ['label' => 'Tracking', 'value' => 'Where supported'],
                ]),
                'sort_order' => $order, 'meta_title' => $title.' | InstaDrop UK',
                'meta_description' => $description,
                'meta_keywords' => strtolower($title).', UK courier, same day delivery',
            ]);
            $section($serviceId, ['section_type' => 'feature_grid', 'eyebrow' => 'HOW THIS SERVICE HELPS', 'heading' => 'A clear delivery option for your consignment', 'body' => $subtitle], array_map(fn ($feature) => ['title' => $feature, 'icon' => 'check'], $features));
            $section($serviceId, ['section_type' => 'steps', 'eyebrow' => 'BOOKING PROCESS', 'heading' => 'How '.$title.' works', 'sort_order' => 1], [
                ['title' => 'Describe the load', 'description' => 'Give accurate dimensions, weight, addresses and handling requirements.'],
                ['title' => 'Confirm the plan', 'description' => 'Dispatch confirms vehicle availability, timing, price and applicable terms.'],
                ['title' => 'Delivery record', 'description' => 'Use the booking reference for available status and proof-of-delivery records.'],
            ]);
            $section($serviceId, ['section_type' => 'info_cards', 'eyebrow' => 'BEFORE REQUESTING A QUOTE', 'heading' => 'Information dispatch needs', 'sort_order' => 2], array_map(fn ($title) => ['title' => $title], ['Full collection and delivery addresses', 'Required date or time window', 'Total item count, size and weight', 'Site access and loading requirements', 'Contents, value and special handling', 'Working contact details at both ends']));
        }

        $coverage = $page([
            'page_type' => 'coverage', 'title' => 'UK Courier Coverage', 'slug' => 'coverage', 'icon' => 'map-pin',
            'hero_badge' => 'MAINLAND UK COVERAGE', 'hero_title' => 'UK Nationwide Dispatch Hubs',
            'hero_description' => 'We arrange courier collections across the UK. Exact availability and collection timing depend on the postcode, vehicle and booking requirements.',
            'meta_title' => 'UK Courier Coverage & Dispatch Hubs',
            'meta_description' => 'Explore InstaDrop courier coverage for same-day, scheduled and dedicated vehicle delivery across the UK.',
            'meta_keywords' => 'UK same day courier coverage, London courier, Birmingham courier, Manchester courier',
        ]);
        $regions = [
            ['London & Greater London', 'Within 30–45 mins', 'Central London, City, Heathrow, M25 Orbital, Croydon and Watford', 'EC, WC, E, N, NW, SE, SW, W, BR, CR, DA, EN, HA, IG, KT, RM, SM, TW, UB'],
            ['Birmingham & West Midlands', 'Within 45 mins', 'Birmingham City Centre, Solihull, Coventry, Wolverhampton and Dudley', 'B, CV, DY, WS, WV'],
            ['Manchester & North West', 'Within 45 mins', 'Manchester, Salford, Trafford, Bolton, Stockport and Warrington', 'M, BL, OL, SK, WA, WN'],
            ['Leeds & West Yorkshire', 'Within 45 mins', 'Leeds, Bradford, Wakefield, Huddersfield and Halifax', 'LS, BD, HD, HG, WF, YO'],
            ['Glasgow & Central Scotland', 'Subject to availability', 'Glasgow, Paisley, Eurocentral and Edinburgh', 'G, PA, FK, EH, ML'],
            ['Bristol & South West', 'Subject to availability', 'Bristol, Avonmouth, Bath, Gloucester, Swindon and Exeter', 'BS, BA, EX, TA, GL'],
            ['Liverpool & Merseyside', 'Within 45 mins', 'Liverpool, Birkenhead, St Helens, Chester and Southport', 'L, CH, PR, WA'],
            ['Newcastle & North East', 'Subject to availability', 'Newcastle, Sunderland, Durham and Teesside', 'NE, SR, DH, TS'],
        ];
        $section($coverage, ['section_type' => 'region_grid', 'eyebrow' => 'REGIONAL DISPATCH CENTRES', 'heading' => 'Select Your Local Pickup Region'], array_map(fn ($region) => ['title' => $region[0], 'badge' => $region[1], 'description' => $region[2], 'value' => $region[3], 'icon' => 'building'], $regions));
        $section($coverage, ['section_type' => 'cta', 'eyebrow' => 'AIRPORT & CARGO TERMINALS', 'heading' => 'Air Freight & Port Same-Day Express', 'body' => 'Ask dispatch about urgent airport, cargo-terminal and port collections for direct onward delivery.', 'cta_label' => 'Book Airport / Port Courier', 'cta_url' => '/instant-quote', 'theme' => 'dark', 'sort_order' => 1]);
    }

    public function down(): void
    {
        Schema::dropIfExists('content_section_items');
        Schema::dropIfExists('content_sections');
        Schema::dropIfExists('content_pages');
    }
};
