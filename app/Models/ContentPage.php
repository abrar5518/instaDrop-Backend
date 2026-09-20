<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class ContentPage extends Model
{
    protected $fillable = [
        'page_type', 'title', 'navigation_title', 'slug', 'icon', 'summary',
        'hero_badge', 'hero_title', 'hero_description', 'hero_image_path', 'hero_image_alt',
        'primary_cta_label', 'primary_cta_url', 'card_badge', 'card_subtitle',
        'card_description', 'card_features', 'card_details', 'card_image_path', 'card_image_alt',
        'status', 'sort_order', 'published_at', 'meta_title', 'meta_description',
        'meta_keywords', 'og_title', 'og_description', 'og_image_path', 'noindex', 'nofollow',
    ];

    protected function casts(): array
    {
        return [
            'card_features' => 'array', 'card_details' => 'array', 'published_at' => 'datetime',
            'noindex' => 'boolean', 'nofollow' => 'boolean', 'sort_order' => 'integer',
        ];
    }

    public function sections(): HasMany
    {
        return $this->hasMany(ContentSection::class)->orderBy('sort_order')->orderBy('id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function publicData(bool $includeSections = true): array
    {
        $data = [
            'type' => $this->page_type,
            'slug' => $this->slug,
            'title' => $this->title,
            'navigationTitle' => $this->navigation_title ?: $this->title,
            'icon' => $this->icon,
            'summary' => $this->summary,
            'hero' => [
                'badge' => $this->hero_badge,
                'title' => $this->hero_title ?: $this->title,
                'description' => $this->hero_description ?: $this->summary,
                'image' => $this->mediaUrl($this->hero_image_path),
                'imageAlt' => $this->hero_image_alt,
                'ctaLabel' => $this->primary_cta_label,
                'ctaUrl' => $this->primary_cta_url,
            ],
            'card' => [
                'badge' => $this->card_badge,
                'subtitle' => $this->card_subtitle ?: $this->summary,
                'description' => $this->card_description ?: $this->hero_description ?: $this->summary,
                'features' => $this->card_features ?: [],
                'details' => $this->card_details ?: [],
                'image' => $this->mediaUrl($this->card_image_path),
                'imageAlt' => $this->card_image_alt,
            ],
            'seo' => [
                'title' => $this->meta_title ?: $this->hero_title ?: $this->title,
                'description' => $this->meta_description ?: $this->hero_description ?: $this->summary,
                'keywords' => $this->meta_keywords,
                'ogTitle' => $this->og_title ?: $this->meta_title ?: $this->title,
                'ogDescription' => $this->og_description ?: $this->meta_description ?: $this->summary,
                'ogImage' => $this->mediaUrl($this->og_image_path ?: $this->hero_image_path ?: $this->card_image_path),
                'noindex' => $this->noindex,
                'nofollow' => $this->nofollow,
            ],
            'updatedAt' => $this->updated_at?->toIso8601String(),
        ];

        if ($includeSections) {
            $data['sections'] = $this->sections
                ->where('is_enabled', true)
                ->filter(fn (ContentSection $section) => $section->hasPublicContent())
                ->map(fn (ContentSection $section) => $section->publicData())
                ->values();
        }

        return $data;
    }

    private function mediaUrl(?string $path): ?string
    {
        return $path ? Storage::disk('public')->url($path) : null;
    }
}
