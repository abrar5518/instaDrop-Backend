<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ServiceDirectorySetting extends Model
{
    protected $fillable = [
        'hero_badge', 'hero_title', 'hero_description', 'cta_label', 'cta_url',
        'meta_title', 'meta_description', 'meta_keywords', 'og_title', 'og_description',
        'og_image_path', 'noindex', 'nofollow',
    ];

    protected function casts(): array
    {
        return ['noindex' => 'boolean', 'nofollow' => 'boolean'];
    }

    public function publicData(): array
    {
        return [
            'type' => 'services_index', 'slug' => 'services', 'title' => 'Courier Services Directory',
            'navigationTitle' => 'Services', 'icon' => 'truck', 'summary' => $this->hero_description,
            'hero' => [
                'badge' => $this->hero_badge, 'title' => $this->hero_title,
                'description' => $this->hero_description, 'image' => null, 'imageAlt' => null,
                'ctaLabel' => $this->cta_label, 'ctaUrl' => $this->cta_url,
            ],
            'card' => ['badge' => null, 'subtitle' => null, 'description' => null, 'features' => [], 'details' => [], 'image' => null, 'imageAlt' => null],
            'seo' => [
                'title' => $this->meta_title ?: $this->hero_title,
                'description' => $this->meta_description ?: $this->hero_description,
                'keywords' => $this->meta_keywords,
                'ogTitle' => $this->og_title ?: $this->meta_title ?: $this->hero_title,
                'ogDescription' => $this->og_description ?: $this->meta_description ?: $this->hero_description,
                'ogImage' => $this->og_image_path ? Storage::disk('public')->url($this->og_image_path) : null,
                'noindex' => $this->noindex, 'nofollow' => $this->nofollow,
            ],
            'sections' => [], 'updatedAt' => $this->updated_at?->toIso8601String(),
        ];
    }
}
