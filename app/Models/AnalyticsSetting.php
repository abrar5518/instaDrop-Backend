<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalyticsSetting extends Model
{
    use HasFactory;

    protected $table = 'analytics_settings';

    protected $fillable = [
        'meta_pixel_id',
        'gtm_container_id',
        'ga4_measurement_id',
        'clarity_project_id',
        'google_search_console_code',
        'is_enabled',
        'site_name',
        'contact_email',
        'contact_phone',
        'contact_address',
        'facebook_url',
        'instagram_url',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    /**
     * Get single record instance for settings.
     */
    public static function getSettings(): static
    {
        return static::firstOrCreate([], [
            'meta_pixel_id' => null,
            'gtm_container_id' => null,
            'ga4_measurement_id' => null,
            'clarity_project_id' => null,
            'google_search_console_code' => null,
            'is_enabled' => true,
            'site_name' => 'InstaDrop Courier Services',
            'contact_email' => 'dispatch@instadrop.co.uk',
            'contact_phone' => '+44 7852 502775',
            'contact_address' => '100 Pall Mall, St. James\'s, London, SW1Y 5NQ',
            'facebook_url' => null,
            'instagram_url' => null,
        ]);
    }

    /**
     * Build Schema.org Organization JSON-LD array.
     */
    public function toOrganizationSchema(): array
    {
        $sameAs = array_values(array_filter([
            $this->facebook_url,
            $this->instagram_url,
        ]));

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $this->site_name ?: 'InstaDrop Courier Services',
            'url' => config('app.url', 'http://localhost:8000'),
        ];

        if ($this->contact_phone || $this->contact_email) {
            $schema['contactPoint'] = [
                [
                    '@type' => 'ContactPoint',
                    'telephone' => $this->contact_phone ?: null,
                    'email' => $this->contact_email ?: null,
                    'contactType' => 'customer service',
                ],
            ];
        }

        if (!empty($this->contact_address)) {
            $schema['address'] = [
                '@type' => 'PostalAddress',
                'streetAddress' => $this->contact_address,
            ];
        }

        if (!empty($sameAs)) {
            $schema['sameAs'] = $sameAs;
        }

        return $schema;
    }

    /**
     * Build Schema.org WebSite JSON-LD array.
     */
    public function toWebSiteSchema(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $this->site_name ?: 'InstaDrop Courier Services',
            'url' => config('app.url', 'http://localhost:8000'),
        ];
    }
}
