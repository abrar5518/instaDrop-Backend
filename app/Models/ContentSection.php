<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class ContentSection extends Model
{
    protected $fillable = [
        'section_type', 'eyebrow', 'heading', 'body', 'image_path', 'image_alt',
        'cta_label', 'cta_url', 'theme', 'sort_order', 'is_enabled',
    ];

    protected function casts(): array
    {
        return ['is_enabled' => 'boolean', 'sort_order' => 'integer'];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(ContentPage::class, 'content_page_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ContentSectionItem::class)->orderBy('sort_order')->orderBy('id');
    }

    public function hasPublicContent(): bool
    {
        return filled($this->eyebrow) || filled($this->heading) || filled($this->body)
            || filled($this->image_path) || filled($this->cta_label) || $this->items->isNotEmpty();
    }

    public function publicData(): array
    {
        return [
            'type' => $this->section_type,
            'eyebrow' => $this->eyebrow,
            'heading' => $this->heading,
            'body' => $this->body,
            'image' => $this->image_path ? Storage::disk('public')->url($this->image_path) : null,
            'imageAlt' => $this->image_alt,
            'ctaLabel' => $this->cta_label,
            'ctaUrl' => $this->cta_url,
            'theme' => $this->theme,
            'items' => $this->items->filter(fn (ContentSectionItem $item) => $item->hasPublicContent())
                ->map(fn (ContentSectionItem $item) => $item->publicData())->values(),
        ];
    }
}
