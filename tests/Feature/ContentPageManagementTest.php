<?php

namespace Tests\Feature;

use App\Models\ContentPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContentPageManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'page_type' => 'service',
            'title' => 'Test Courier Service',
            'navigation_title' => 'Test Service',
            'slug' => 'test-courier-service',
            'icon' => 'truck',
            'summary' => 'A test courier service summary.',
            'hero_badge' => 'TEST SERVICE',
            'hero_title' => 'Test Courier Service',
            'hero_description' => 'A complete service description for customers.',
            'primary_cta_label' => 'Get a quote',
            'primary_cta_url' => '/instant-quote',
            'card_badge' => 'Testing',
            'card_subtitle' => 'A short card subtitle.',
            'card_description' => 'A useful directory card description.',
            'card_features_text' => "First feature\nSecond feature",
            'card_details_text' => "Collection|Confirmed by dispatch\nJourney|Direct",
            'status' => 'published',
            'sort_order' => 25,
            'meta_title' => 'Test Courier Service | InstaDrop',
            'meta_description' => 'SEO description for the test courier service.',
            'meta_keywords' => 'test courier, UK delivery',
            'sections' => [
                [
                    'section_type' => 'feature_grid',
                    'eyebrow' => 'BENEFITS',
                    'heading' => 'First feature section',
                    'theme' => 'light',
                    'is_enabled' => '1',
                    'items' => [
                        ['title' => 'Fast collection', 'description' => 'Dispatch confirms timing.', 'icon' => 'clock'],
                    ],
                ],
                [
                    'section_type' => 'feature_grid',
                    'heading' => 'Second feature section',
                    'theme' => 'soft',
                    'is_enabled' => '1',
                    'items' => [
                        ['title' => 'Direct journey', 'description' => 'No depot handling.', 'icon' => 'truck'],
                    ],
                ],
            ],
        ], $overrides);
    }

    public function test_admin_routes_require_authentication(): void
    {
        $this->get('/admin/content-pages')->assertRedirect('/admin/login');
        $this->postJson('/admin/content-pages', [])->assertUnauthorized();
    }

    public function test_seeded_services_and_managed_pages_are_public(): void
    {
        $this->getJson('/api/v1/services')->assertOk()
            ->assertJsonPath('data.0.slug', 'same-day-delivery');
        $this->getJson('/api/v1/services/same-day-delivery')->assertOk()
            ->assertJsonPath('data.sections.0.type', 'feature_grid');
        $this->getJson('/api/v1/managed-pages/services')->assertOk()
            ->assertJsonPath('data.type', 'services_index');
        $this->getJson('/api/v1/managed-pages/coverage')->assertOk()
            ->assertJsonPath('data.sections.0.type', 'region_grid');
    }

    public function test_admin_can_publish_a_service_with_repeated_sections(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin)
            ->post('/admin/content-pages', $this->payload())
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $page = ContentPage::where('slug', 'test-courier-service')->firstOrFail();
        $this->assertCount(2, $page->sections);
        $this->assertSame('First feature section', $page->sections[0]->heading);
        $this->assertSame('Second feature section', $page->sections[1]->heading);

        $this->getJson('/api/v1/services/test-courier-service')->assertOk()
            ->assertJsonCount(2, 'data.sections')
            ->assertJsonPath('data.seo.title', 'Test Courier Service | InstaDrop')
            ->assertJsonPath('data.seo.keywords', 'test courier, UK delivery')
            ->assertJsonPath('data.sections.1.heading', 'Second feature section');
    }

    public function test_draft_and_disabled_or_empty_sections_are_not_public(): void
    {
        $draftPayload = $this->payload(['slug' => 'draft-service', 'status' => 'draft']);
        $this->actingAs($this->admin)->post('/admin/content-pages', $draftPayload)->assertSessionHasNoErrors();
        $this->getJson('/api/v1/services/draft-service')->assertNotFound();
        $this->getJson('/api/v1/services')->assertJsonMissing(['slug' => 'draft-service']);

        $payload = $this->payload([
            'slug' => 'filtered-sections',
            'sections' => [
                ['section_type' => 'rich_text', 'heading' => 'Visible', 'theme' => 'light', 'is_enabled' => '1'],
                ['section_type' => 'rich_text', 'heading' => 'Disabled', 'theme' => 'light'],
                ['section_type' => 'rich_text', 'theme' => 'light', 'is_enabled' => '1'],
            ],
        ]);
        $this->actingAs($this->admin)->post('/admin/content-pages', $payload)->assertSessionHasNoErrors();
        $this->getJson('/api/v1/services/filtered-sections')->assertOk()
            ->assertJsonCount(1, 'data.sections')
            ->assertJsonPath('data.sections.0.heading', 'Visible');
    }

    public function test_image_alt_text_is_required_for_uploaded_images(): void
    {
        Storage::fake('public');
        $payload = $this->payload([
            'slug' => 'missing-image-alt',
            'sections' => [[
                'section_type' => 'rich_text',
                'heading' => 'Image section',
                'theme' => 'light',
                'is_enabled' => '1',
                'image' => UploadedFile::fake()->image('section.jpg', 1200, 800),
                'image_alt' => '',
            ]],
        ]);

        $this->actingAs($this->admin)->post('/admin/content-pages', $payload)
            ->assertSessionHasErrors('sections.0.image_alt');
        $this->assertDatabaseMissing('content_pages', ['slug' => 'missing-image-alt']);
    }

    public function test_core_pages_cannot_be_deleted_but_services_can(): void
    {
        $coverage = ContentPage::where('page_type', 'coverage')->firstOrFail();
        $service = ContentPage::where('page_type', 'service')->firstOrFail();

        $this->actingAs($this->admin)->delete('/admin/content-pages/'.$coverage->id)->assertForbidden();
        $this->actingAs($this->admin)->delete('/admin/content-pages/'.$service->id)->assertRedirect('/admin/content-pages');
        $this->assertDatabaseMissing('content_pages', ['id' => $service->id]);
    }
}
