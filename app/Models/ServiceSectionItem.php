<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ServiceSectionItem extends Model
{
    protected $fillable = ['badge', 'title', 'body', 'link_label', 'link_url', 'image_path', 'image_alt', 'sort_order'];
    protected function casts(): array { return ['sort_order' => 'integer']; }
    public function section(): BelongsTo { return $this->belongsTo(ServiceSection::class, 'service_section_id'); }
    public function hasPublicContent(): bool { return filled($this->badge) || filled($this->title) || filled($this->body) || filled($this->link_label) || filled($this->link_url) || filled($this->image_path); }
    public function publicData(): array
    {
        return [
            'badge' => $this->badge, 'title' => $this->title, 'body' => $this->body,
            'linkLabel' => $this->link_label, 'linkUrl' => $this->link_url,
            'image' => $this->image_path ? Storage::disk('public')->url($this->image_path) : null,
            'imageAlt' => $this->image_alt,
        ];
    }
}
