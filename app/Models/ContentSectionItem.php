<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ContentSectionItem extends Model
{
    protected $fillable = [
        'title', 'subtitle', 'description', 'badge', 'value', 'icon', 'image_path',
        'image_alt', 'link_label', 'link_url', 'metadata', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['metadata' => 'array', 'sort_order' => 'integer'];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(ContentSection::class, 'content_section_id');
    }

    public function hasPublicContent(): bool
    {
        return filled($this->title) || filled($this->subtitle) || filled($this->description)
            || filled($this->badge) || filled($this->value) || filled($this->icon)
            || filled($this->image_path) || filled($this->link_label) || filled($this->link_url);
    }

    public function publicData(): array
    {
        return [
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'description' => $this->description,
            'badge' => $this->badge,
            'value' => $this->value,
            'icon' => $this->icon,
            'image' => $this->image_path ? Storage::disk('public')->url($this->image_path) : null,
            'imageAlt' => $this->image_alt,
            'linkLabel' => $this->link_label,
            'linkUrl' => $this->link_url,
            'metadata' => $this->metadata ?: null,
        ];
    }
}
