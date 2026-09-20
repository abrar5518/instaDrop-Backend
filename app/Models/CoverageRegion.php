<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoverageRegion extends Model
{
    protected $fillable = ['title', 'timing', 'hubs', 'postcodes', 'icon', 'is_enabled', 'sort_order'];
    protected function casts(): array { return ['is_enabled' => 'boolean', 'sort_order' => 'integer']; }
    public function page(): BelongsTo { return $this->belongsTo(CoveragePage::class, 'coverage_page_id'); }
    public function publicData(): array { return ['title' => $this->title, 'timing' => $this->timing, 'hubs' => $this->hubs, 'postcodes' => $this->postcodes, 'icon' => $this->icon]; }
}
