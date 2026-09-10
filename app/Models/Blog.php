<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Blog extends Model
{
    protected $fillable = ['title', 'slug', 'category', 'excerpt', 'content', 'image_path', 'image_alt', 'status', 'is_featured', 'published_at', 'meta_title', 'meta_description', 'meta_keywords', 'og_title', 'og_description', 'og_image_path', 'noindex', 'nofollow'];

    protected function casts(): array
    {
        return ['published_at' => 'datetime', 'is_featured' => 'boolean', 'noindex' => 'boolean', 'nofollow' => 'boolean'];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function publicData(bool $includeContent = false): array
    {
        $data = [
            'slug' => $this->slug, 'title' => $this->title, 'category' => $this->category,
            'description' => $this->excerpt,
            'image' => $this->image_path ? Storage::disk('public')->url($this->image_path) : null,
            'alt' => $this->image_alt ?: $this->title,
            'date' => $this->published_at?->format('j F Y'),
            'publishedAt' => $this->published_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
            'readTime' => max(1, (int) ceil(str_word_count(strip_tags($this->content)) / 200)).' min read',
            'featured' => $this->is_featured,
            'noindex' => $this->noindex,
        ];
        if ($includeContent) {
            $data['content'] = $this->content;
            $data['seo'] = [
                'title' => $this->meta_title ?: $this->title,
                'description' => $this->meta_description ?: $this->excerpt,
                'keywords' => $this->meta_keywords,
                'ogTitle' => $this->og_title ?: $this->meta_title ?: $this->title,
                'ogDescription' => $this->og_description ?: $this->meta_description ?: $this->excerpt,
                'ogImage' => $this->og_image_path ? Storage::disk('public')->url($this->og_image_path) : $data['image'],
                'noindex' => $this->noindex, 'nofollow' => $this->nofollow,
            ];
        }
        return $data;
    }
}
