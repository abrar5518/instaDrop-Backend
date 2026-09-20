<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Service extends Model
{
    protected $fillable = [
        'title', 'navigation_title', 'slug', 'icon', 'summary', 'card_badge', 'card_subtitle',
        'card_description', 'card_features', 'card_details', 'hero_eyebrow', 'hero_title',
        'hero_description', 'hero_primary_label', 'hero_primary_url', 'hero_secondary_label',
        'hero_secondary_url', 'hero_points', 'hero_image_path', 'hero_image_alt', 'route_kicker',
        'route_counter', 'route_title', 'collection_label', 'collection_detail', 'delivery_label',
        'delivery_detail', 'route_footer', 'route_status', 'notice_title', 'notice_body',
        'sidebar_title', 'sidebar_body', 'sidebar_cta_label', 'sidebar_cta_url',
        'sidebar_helpful_details', 'bottom_cta_title', 'bottom_cta_body', 'bottom_cta_label',
        'bottom_cta_url', 'status', 'sort_order', 'published_at', 'meta_title', 'meta_description',
        'meta_keywords', 'og_title', 'og_description', 'og_image_path', 'noindex', 'nofollow',
    ];

    protected function casts(): array
    {
        return [
            'card_features' => 'array', 'card_details' => 'array', 'hero_points' => 'array',
            'published_at' => 'datetime', 'sort_order' => 'integer', 'noindex' => 'boolean',
            'nofollow' => 'boolean',
        ];
    }

    public function sections(): HasMany
    {
        return $this->hasMany(ServiceSection::class)->orderBy('sort_order')->orderBy('id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function publicData(bool $includeSections = true): array
    {
        $data = [
            'type' => 'service', 'slug' => $this->slug, 'title' => $this->title,
            'navigationTitle' => $this->navigation_title ?: $this->title, 'icon' => $this->icon,
            'summary' => $this->summary,
            'hero' => [
                'eyebrow' => $this->hero_eyebrow, 'title' => $this->hero_title ?: $this->title,
                'description' => $this->hero_description ?: $this->summary,
                'primaryLabel' => $this->hero_primary_label, 'primaryUrl' => $this->hero_primary_url,
                'secondaryLabel' => $this->hero_secondary_label, 'secondaryUrl' => $this->hero_secondary_url,
                'points' => $this->hero_points ?: [], 'image' => $this->mediaUrl($this->hero_image_path),
                'imageAlt' => $this->hero_image_alt,
            ],
            'routeVisual' => [
                'kicker' => $this->route_kicker, 'counter' => $this->route_counter, 'title' => $this->route_title,
                'collectionLabel' => $this->collection_label, 'collectionDetail' => $this->collection_detail,
                'deliveryLabel' => $this->delivery_label, 'deliveryDetail' => $this->delivery_detail,
                'footer' => $this->route_footer, 'status' => $this->route_status,
            ],
            'notice' => ['title' => $this->notice_title, 'body' => $this->notice_body],
            'sidebar' => [
                'title' => $this->sidebar_title, 'body' => $this->sidebar_body,
                'ctaLabel' => $this->sidebar_cta_label, 'ctaUrl' => $this->sidebar_cta_url,
                'helpfulDetails' => $this->sidebar_helpful_details,
            ],
            'bottomCta' => [
                'title' => $this->bottom_cta_title, 'body' => $this->bottom_cta_body,
                'label' => $this->bottom_cta_label, 'url' => $this->bottom_cta_url,
            ],
            'card' => [
                'badge' => $this->card_badge, 'subtitle' => $this->card_subtitle ?: $this->summary,
                'description' => $this->card_description ?: $this->hero_description ?: $this->summary,
                'features' => $this->card_features ?: [], 'details' => $this->card_details ?: [],
                'image' => null, 'imageAlt' => null,
            ],
            'seo' => [
                'title' => $this->meta_title ?: $this->hero_title ?: $this->title,
                'description' => $this->meta_description ?: $this->hero_description ?: $this->summary,
                'keywords' => $this->meta_keywords,
                'ogTitle' => $this->og_title ?: $this->meta_title ?: $this->title,
                'ogDescription' => $this->og_description ?: $this->meta_description ?: $this->summary,
                'ogImage' => $this->mediaUrl($this->og_image_path ?: $this->hero_image_path),
                'noindex' => $this->noindex, 'nofollow' => $this->nofollow,
            ],
            'updatedAt' => $this->updated_at?->toIso8601String(),
        ];
        if ($includeSections) {
            $data['sections'] = $this->sections->where('is_enabled', true)
                ->filter(fn (ServiceSection $section) => $section->hasPublicContent())
                ->map(fn (ServiceSection $section) => $section->publicData())->values();
        }
        return $data;
    }

    private function mediaUrl(?string $path): ?string
    {
        return $path ? Storage::disk('public')->url($path) : null;
    }
}
