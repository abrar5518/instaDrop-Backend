<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class ServiceSection extends Model
{
    protected $fillable = ['section_type', 'anchor_id', 'label', 'heading', 'intro', 'body', 'callout_heading', 'callout_body', 'image_path', 'image_alt', 'show_in_sidebar', 'is_enabled', 'sort_order'];

    protected function casts(): array
    {
        return ['show_in_sidebar' => 'boolean', 'is_enabled' => 'boolean', 'sort_order' => 'integer'];
    }

    public function service(): BelongsTo { return $this->belongsTo(Service::class); }
    public function items(): HasMany { return $this->hasMany(ServiceSectionItem::class)->orderBy('sort_order')->orderBy('id'); }

    public function hasPublicContent(): bool
    {
        return filled($this->label) || filled($this->heading) || filled($this->intro) || filled($this->body)
            || filled($this->callout_heading) || filled($this->callout_body) || filled($this->image_path)
            || $this->items->contains(fn (ServiceSectionItem $item) => $item->hasPublicContent());
    }

    public function publicData(): array
    {
        return [
            'type' => $this->section_type, 'anchorId' => $this->anchor_id, 'label' => $this->label,
            'heading' => $this->heading, 'intro' => $this->intro, 'body' => $this->body,
            'calloutHeading' => $this->callout_heading, 'calloutBody' => $this->callout_body,
            'image' => $this->image_path ? Storage::disk('public')->url($this->image_path) : null,
            'imageAlt' => $this->image_alt, 'showInSidebar' => $this->show_in_sidebar,
            'items' => $this->items->filter(fn (ServiceSectionItem $item) => $item->hasPublicContent())
                ->map(fn (ServiceSectionItem $item) => $item->publicData())->values(),
        ];
    }
}
