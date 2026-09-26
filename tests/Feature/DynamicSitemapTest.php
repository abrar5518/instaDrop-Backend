<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DynamicSitemapTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_contains_every_published_indexable_blog_and_service_only(): void
    {
        Blog::query()->create([
            'title' => 'Published sitemap article',
            'slug' => 'published-sitemap-article',
            'category' => 'Guides',
            'excerpt' => 'Published and indexable.',
            'content' => '<p>Article</p>',
            'status' => 'published',
            'published_at' => now(),
            'noindex' => false,
        ]);
        Blog::query()->create([
            'title' => 'Noindex sitemap article',
            'slug' => 'noindex-sitemap-article',
            'category' => 'Guides',
            'excerpt' => 'Published but excluded.',
            'content' => '<p>Article</p>',
            'status' => 'published',
            'published_at' => now(),
            'noindex' => true,
        ]);
        Service::query()->create([
            'title' => 'Published sitemap service',
            'navigation_title' => 'Published sitemap service',
            'slug' => 'published-sitemap-service',
            'status' => 'published',
            'published_at' => now(),
            'noindex' => false,
        ]);
        Service::query()->create([
            'title' => 'Draft sitemap service',
            'navigation_title' => 'Draft sitemap service',
            'slug' => 'draft-sitemap-service',
            'status' => 'draft',
            'noindex' => false,
        ]);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('content-type', 'application/xml; charset=UTF-8')
            ->assertSee('https://instadrop.uk/blog/published-sitemap-article', false)
            ->assertSee('https://instadrop.uk/published-sitemap-service', false)
            ->assertDontSee('noindex-sitemap-article')
            ->assertDontSee('draft-sitemap-service');
    }
}
