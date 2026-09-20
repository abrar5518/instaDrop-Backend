<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class CoveragePage extends Model
{
    protected $fillable = [
        'hero_badge', 'hero_title', 'hero_description', 'regions_eyebrow', 'regions_heading',
        'bottom_cta_title', 'bottom_cta_body', 'bottom_cta_label', 'bottom_cta_url',
        'meta_title', 'meta_description', 'meta_keywords', 'og_title', 'og_description',
        'og_image_path', 'noindex', 'nofollow', 'status', 'published_at',
    ];

    protected function casts(): array
    {
        return ['published_at' => 'datetime', 'noindex' => 'boolean', 'nofollow' => 'boolean'];
    }

    public function regions(): HasMany { return $this->hasMany(CoverageRegion::class)->orderBy('sort_order')->orderBy('id'); }
    public function sections(): HasMany { return $this->hasMany(CoverageSection::class)->orderBy('sort_order')->orderBy('id'); }
    public function scopePublished(Builder $query): Builder { return $query->where('status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now()); }

    public function publicData(): array
    {
        return [
            'type' => 'coverage', 'slug' => 'coverage', 'title' => $this->hero_title,
            'navigationTitle' => 'Coverage', 'icon' => 'map-pin', 'summary' => $this->hero_description,
            'hero' => ['badge' => $this->hero_badge, 'title' => $this->hero_title, 'description' => $this->hero_description, 'image' => null, 'imageAlt' => null, 'ctaLabel' => null, 'ctaUrl' => null],
            'regionsEyebrow' => $this->regions_eyebrow, 'regionsHeading' => $this->regions_heading,
            'regions' => $this->regions->where('is_enabled', true)->map(fn (CoverageRegion $region) => $region->publicData())->values(),
            'sections' => $this->sections->where('is_enabled', true)->filter(fn (CoverageSection $section) => $section->hasPublicContent())->map(fn (CoverageSection $section) => $section->publicData())->values(),
            'bottomCta' => ['title' => $this->bottom_cta_title, 'body' => $this->bottom_cta_body, 'label' => $this->bottom_cta_label, 'url' => $this->bottom_cta_url],
            'card' => ['badge' => null, 'subtitle' => null, 'description' => null, 'features' => [], 'details' => [], 'image' => null, 'imageAlt' => null],
            'seo' => [
                'title' => $this->meta_title ?: $this->hero_title, 'description' => $this->meta_description ?: $this->hero_description,
                'keywords' => $this->meta_keywords, 'ogTitle' => $this->og_title ?: $this->meta_title ?: $this->hero_title,
                'ogDescription' => $this->og_description ?: $this->meta_description ?: $this->hero_description,
                'ogImage' => $this->og_image_path ? Storage::disk('public')->url($this->og_image_path) : null,
                'noindex' => $this->noindex, 'nofollow' => $this->nofollow,
            ],
            'updatedAt' => $this->updated_at?->toIso8601String(),
        ];
    }
}
