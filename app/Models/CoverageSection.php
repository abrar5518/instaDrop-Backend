<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoverageSection extends Model
{
    protected $fillable = ['eyebrow', 'heading', 'body', 'cta_label', 'cta_url', 'theme', 'is_enabled', 'sort_order'];
    protected function casts(): array { return ['is_enabled' => 'boolean', 'sort_order' => 'integer']; }
    public function page(): BelongsTo { return $this->belongsTo(CoveragePage::class, 'coverage_page_id'); }
    public function hasPublicContent(): bool { return filled($this->eyebrow) || filled($this->heading) || filled($this->body) || filled($this->cta_label); }
    public function publicData(): array { return ['eyebrow' => $this->eyebrow, 'heading' => $this->heading, 'body' => $this->body, 'ctaLabel' => $this->cta_label, 'ctaUrl' => $this->cta_url, 'theme' => $this->theme]; }
}
