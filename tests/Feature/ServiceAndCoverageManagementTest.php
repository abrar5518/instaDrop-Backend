<?php

namespace Tests\Feature;

use App\Models\CoveragePage;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ServiceAndCoverageManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
    }

    private function servicePayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Test Courier Service', 'navigation_title' => 'Test Service',
            'slug' => 'test-courier-service', 'icon' => 'truck', 'summary' => 'Service summary.',
            'card_badge' => 'Direct', 'card_subtitle' => 'Card subtitle', 'card_description' => 'Card description',
            'card_features_text' => "Dedicated vehicle\nProof of delivery", 'card_details_text' => "Collection|Confirmed\nJourney|Direct",
            'hero_eyebrow' => 'COURIER SERVICES / TEST', 'hero_title' => 'A test service arranged around your deadline.',
            'hero_description' => 'A complete service description.', 'hero_primary_label' => 'Get a quote',
            'hero_primary_url' => '/instant-quote', 'hero_secondary_label' => 'Call dispatch',
            'hero_secondary_url' => 'tel:+447852502775', 'hero_points_text' => "Dedicated vehicle\nUK-wide journeys",
            'route_kicker' => 'THE DIRECT ROUTE', 'route_counter' => '01 / 02', 'route_title' => "One collection.\nOne destination.",
            'collection_label' => 'Collection', 'collection_detail' => 'Confirmed by dispatch',
            'delivery_label' => 'Delivery', 'delivery_detail' => 'Recipient confirmation', 'route_footer' => 'ONE JOURNEY', 'route_status' => 'DIRECT',
            'notice_title' => 'Need a precise collection window?', 'notice_body' => 'Share the postcodes and deadline.',
            'sidebar_title' => 'Have an urgent delivery?', 'sidebar_body' => 'Send the journey details.',
            'sidebar_cta_label' => 'Request a quote', 'sidebar_cta_url' => '/instant-quote',
            'sidebar_helpful_details' => 'Postcodes, dimensions, weight and deadline.',
            'bottom_cta_title' => 'Tell us what needs to move.', 'bottom_cta_body' => 'Dispatch will confirm options.',
            'bottom_cta_label' => 'Get my quote', 'bottom_cta_url' => '/instant-quote',
            'status' => 'published', 'sort_order' => 30, 'meta_title' => 'Test service SEO',
            'meta_description' => 'Test service search description.', 'meta_keywords' => 'test courier, UK',
            'sections' => [
                ['section_type' => 'content', 'anchor_id' => 'overview', 'label' => 'Service overview', 'heading' => 'Service overview heading', 'intro' => 'Intro paragraph.', 'body' => "Paragraph one.\n\nParagraph two.", 'show_in_sidebar' => '1', 'is_enabled' => '1'],
                ['section_type' => 'steps', 'anchor_id' => 'how', 'heading' => 'How booking works', 'show_in_sidebar' => '1', 'is_enabled' => '1', 'items' => [
                    ['badge' => '01 / REQUEST', 'title' => 'Tell us the journey', 'body' => 'Provide both postcodes.'],
                    ['badge' => '02 / CONFIRM', 'title' => 'Agree the booking', 'body' => 'Dispatch confirms timing.'],
                ]],
                ['section_type' => 'faq', 'anchor_id' => 'questions', 'heading' => 'Frequently asked questions', 'is_enabled' => '1', 'items' => [
                    ['title' => 'Can I book at any time?', 'body' => 'The dispatch desk is available 24/7.'],
                ]],
            ],
        ], $overrides);
    }

    public function test_services_and_coverage_have_separate_admin_routes(): void
    {
        $this->get('/admin/services')->assertRedirect('/admin/login');
        $this->get('/admin/coverage')->assertRedirect('/admin/login');
        $this->actingAs($this->admin)->get('/admin/services')->assertOk()->assertSee('Only courier service pages');
        $this->actingAs($this->admin)->get('/admin/coverage')->assertOk()->assertSee('Coverage is completely separate');
    }

    public function test_seeded_public_apis_use_separate_models(): void
    {
        $this->assertSame(7, Service::count());
        $this->assertSame(1, CoveragePage::count());
        $this->getJson('/api/v1/services')->assertOk()->assertJsonPath('data.0.slug', 'same-day-delivery');
        $this->getJson('/api/v1/services-page')->assertOk()->assertJsonPath('data.type', 'services_index');
        $this->getJson('/api/v1/coverage')->assertOk()->assertJsonPath('data.type', 'coverage')->assertJsonPath('data.regions.0.title', 'London & Greater London');
    }

    public function test_admin_can_publish_template_service_with_repeatable_blocks(): void
    {
        $this->actingAs($this->admin)->post('/admin/services', $this->servicePayload())->assertSessionHasNoErrors()->assertRedirect();
        $service = Service::where('slug', 'test-courier-service')->firstOrFail();
        $this->assertCount(3, $service->sections);
        $this->assertCount(2, $service->sections[1]->items);
        $this->getJson('/api/v1/services/test-courier-service')->assertOk()
            ->assertJsonPath('data.hero.eyebrow', 'COURIER SERVICES / TEST')
            ->assertJsonPath('data.routeVisual.status', 'DIRECT')
            ->assertJsonPath('data.sections.1.type', 'steps')
            ->assertJsonPath('data.sections.2.items.0.title', 'Can I book at any time?')
            ->assertJsonPath('data.seo.keywords', 'test courier, UK');
    }

    public function test_drafts_and_disabled_blocks_are_hidden(): void
    {
        $this->actingAs($this->admin)->post('/admin/services', $this->servicePayload(['slug' => 'draft-service', 'status' => 'draft']))->assertSessionHasNoErrors();
        $this->getJson('/api/v1/services/draft-service')->assertNotFound();
        $payload = $this->servicePayload(['slug' => 'filtered-service', 'sections' => [
            ['section_type' => 'content', 'heading' => 'Visible', 'is_enabled' => '1'],
            ['section_type' => 'content', 'heading' => 'Hidden'],
            ['section_type' => 'content'],
        ]]);
        $this->actingAs($this->admin)->post('/admin/services', $payload)->assertSessionHasNoErrors();
        $this->getJson('/api/v1/services/filtered-service')->assertOk()->assertJsonCount(1, 'data.sections')->assertJsonPath('data.sections.0.heading', 'Visible');
    }

    public function test_alt_text_is_required_for_service_images(): void
    {
        Storage::fake('public');
        $payload = $this->servicePayload(['slug' => 'missing-alt', 'sections' => [[
            'section_type' => 'content', 'heading' => 'Image block', 'is_enabled' => '1',
            'image' => UploadedFile::fake()->image('service.jpg', 1200, 800), 'image_alt' => '',
        ]]]);
        $this->actingAs($this->admin)->post('/admin/services', $payload)->assertSessionHasErrors('sections.0.image_alt');
        $this->assertDatabaseMissing('services', ['slug' => 'missing-alt']);
    }

    public function test_coverage_updates_only_coverage_tables(): void
    {
        $serviceCount = Service::count();
        $this->actingAs($this->admin)->put('/admin/coverage', [
            'hero_badge' => 'UK COVERAGE', 'hero_title' => 'Updated UK Coverage', 'hero_description' => 'Updated coverage description.',
            'regions_eyebrow' => 'REGIONS', 'regions_heading' => 'Choose your region',
            'bottom_cta_title' => 'Need another location?', 'bottom_cta_body' => 'Ask dispatch.', 'bottom_cta_label' => 'Get quote', 'bottom_cta_url' => '/instant-quote',
            'meta_title' => 'Coverage SEO', 'meta_description' => 'Coverage search description.', 'status' => 'published',
            'regions' => [['title' => 'Test Region', 'timing' => 'Confirmed by dispatch', 'hubs' => 'Test Hub', 'postcodes' => 'TE1', 'icon' => 'building', 'is_enabled' => '1']],
            'sections' => [['eyebrow' => 'PORTS', 'heading' => 'Port courier coverage', 'body' => 'Ask dispatch about port collections.', 'cta_label' => 'Ask dispatch', 'cta_url' => '/contact', 'theme' => 'dark', 'is_enabled' => '1']],
        ])->assertSessionHasNoErrors();
        $this->assertSame($serviceCount, Service::count());
        $this->getJson('/api/v1/coverage')->assertOk()->assertJsonPath('data.hero.title', 'Updated UK Coverage')->assertJsonPath('data.regions.0.title', 'Test Region')->assertJsonPath('data.sections.0.heading', 'Port courier coverage');
    }
}
