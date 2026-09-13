<?php

namespace Tests\Feature;

use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SystemSettingsPublishingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'business_name' => 'InstaDrop Courier Services',
            'hotline_phone' => '+447852502775',
            'support_email' => 'dispatch@instadrop.uk',
            'office_address' => 'United Kingdom',
            'admin_whatsapp_number' => '+447852502775',
            'opening_hours' => '24/7 Dispatch Desk',
            'currency_code' => 'GBP',
            'vat_rate' => '20.00',
            'facebook_url' => 'https://facebook.com/instadrop',
            'facebook_enabled' => '1',
            'instagram_url' => 'https://instagram.com/instadrop',
            'instagram_enabled' => '1',
            'google_tag_manager_id' => 'gtm-abc123',
            'google_tag_manager_enabled' => '1',
            'google_analytics_id' => 'g-abc123def4',
            'google_analytics_enabled' => '1',
            'meta_pixel_id' => '1234567890',
            'meta_pixel_enabled' => '1',
            'header_logo' => UploadedFile::fake()->image('header.png', 400, 120),
            'footer_logo' => UploadedFile::fake()->image('footer.jpg', 400, 120),
            'favicon' => UploadedFile::fake()->image('favicon.png', 64, 64),
        ], $overrides);
    }

    public function test_branding_social_profiles_and_tracking_are_saved_and_published(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create())
            ->post('/admin/settings', $this->payload())
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $setting = SystemSetting::firstOrFail();
        $this->assertTrue($setting->facebook_enabled);
        $this->assertFalse($setting->x_enabled);
        $this->assertSame('GTM-ABC123', $setting->google_tag_manager_id);
        $this->assertSame('G-ABC123DEF4', $setting->google_analytics_id);
        $this->assertSame('1234567890', $setting->meta_pixel_id);
        Storage::disk('public')->assertExists($setting->header_logo_path);
        Storage::disk('public')->assertExists($setting->footer_logo_path);
        Storage::disk('public')->assertExists($setting->favicon_path);

        $this->getJson('/api/v1/settings/public')
            ->assertOk()
            ->assertJsonPath('social_links.facebook', 'https://facebook.com/instadrop')
            ->assertJsonPath('social_links.instagram', 'https://instagram.com/instadrop')
            ->assertJsonPath('social_links.x', null)
            ->assertJsonPath('tracking.gtm_id', 'GTM-ABC123')
            ->assertJsonPath('tracking.ga_id', 'G-ABC123DEF4')
            ->assertJsonPath('tracking.meta_pixel_id', '1234567890');
    }

    public function test_enabled_integrations_require_valid_values(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create())
            ->post('/admin/settings', $this->payload([
                'facebook_url' => '',
                'google_tag_manager_id' => 'invalid',
                'google_analytics_id' => 'UA-123',
                'meta_pixel_id' => 'pixel-123',
            ]))
            ->assertSessionHasErrors([
                'facebook_url',
                'google_tag_manager_id',
                'google_analytics_id',
                'meta_pixel_id',
            ]);

        $this->assertDatabaseMissing('system_settings', [
            'google_tag_manager_id' => 'invalid',
            'google_analytics_id' => 'UA-123',
            'meta_pixel_id' => 'pixel-123',
        ]);
    }
}
