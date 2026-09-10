<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\User;
use App\Services\BlogHtmlSanitizer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BlogManagementTest extends TestCase
{
    use DatabaseTransactions;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Integration test blog', 'slug' => 'integration-test-'.bin2hex(random_bytes(5)),
            'category' => 'Testing', 'excerpt' => 'A temporary test article.',
            'content' => '<h1>Test article</h1><p><strong>Bold</strong> <a href="/contact">Contact</a></p><table><tbody><tr><td>Cell</td></tr></tbody></table>',
            'image' => UploadedFile::fake()->image('cover.jpg', 1280, 630),
            'image_alt' => 'Test cover', 'status' => 'draft',
            'meta_title' => 'Custom SEO title', 'meta_description' => 'Custom search description',
            'meta_keywords' => 'courier, test',
        ], $overrides);
    }

    public function test_admin_routes_and_uploads_require_authentication(): void
    {
        $this->get('/admin/blogs')->assertRedirect('/admin/login');
        $this->postJson('/admin/blogs', [])->assertUnauthorized();
        $this->postJson('/admin/blogs/upload', [])->assertUnauthorized();
    }

    public function test_draft_publish_update_unpublish_and_delete_lifecycle(): void
    {
        Storage::fake('public');
        $this->actingAs(User::firstOrFail());
        $data = $this->payload();
        $this->post('/admin/blogs', $data)->assertSessionHasNoErrors()->assertRedirect();
        $blog = Blog::where('slug', $data['slug'])->firstOrFail();
        Storage::disk('public')->assertExists($blog->image_path);
        $this->getJson('/api/v1/blogs/'.$blog->slug)->assertNotFound();
        $this->getJson('/api/v1/blogs')->assertJsonMissing(['slug' => $blog->slug]);
        unset($data['image']);
        $data['status'] = 'published';
        $this->put('/admin/blogs/'.$blog->id, $data)->assertSessionHasNoErrors();
        $this->getJson('/api/v1/blogs/'.$blog->slug)->assertOk()->assertJsonPath('data.seo.title', 'Custom SEO title')->assertJsonPath('data.seo.keywords', 'courier, test')->assertJsonPath('data.seo.noindex', false);
        $data['title'] = 'Updated title';
        $data['noindex'] = '1';
        $this->put('/admin/blogs/'.$blog->id, $data)->assertSessionHasNoErrors();
        $this->getJson('/api/v1/blogs/'.$blog->slug)->assertJsonPath('data.title', 'Updated title')->assertJsonPath('data.seo.noindex', true);
        $data['status'] = 'draft';
        $this->put('/admin/blogs/'.$blog->id, $data)->assertSessionHasNoErrors();
        $this->getJson('/api/v1/blogs/'.$blog->slug)->assertNotFound();
        $this->delete('/admin/blogs/'.$blog->id)->assertRedirect('/admin/blogs');
        $this->assertDatabaseMissing('blogs', ['id' => $blog->id]);
    }

    public function test_validation_rejects_duplicate_slugs_and_non_image_uploads(): void
    {
        Storage::fake('public');
        $this->actingAs(User::firstOrFail());
        $this->post('/admin/blogs', $this->payload(['slug' => Blog::firstOrFail()->slug]))->assertSessionHasErrors('slug');
        $this->postJson('/admin/blogs/upload', ['files' => [UploadedFile::fake()->create('payload.svg', 1, 'image/svg+xml')]])->assertUnprocessable();
        $this->post('/admin/blogs', $this->payload(['content' => '<script>alert(1)</script>']))->assertSessionHasErrors('content');
    }

    public function test_editor_uploads_and_sanitization_keep_rich_content_safe(): void
    {
        Storage::fake('public');
        $this->actingAs(User::firstOrFail());
        $this->postJson('/admin/blogs/upload', ['files' => [UploadedFile::fake()->image('inline.png')]])->assertOk()->assertJsonPath('success', true);
        $safe = app(BlogHtmlSanitizer::class)->clean('<h2>Heading</h2><p style="text-align:center"><b>Bold</b><a href="/contact">Internal</a><a href="javascript:alert(1)">Bad</a></p><table><tr><td>Data</td></tr></table><img src="https://example.com/image.jpg" onerror="alert(1)"><script>alert(1)</script>');
        $this->assertStringContainsString('<h2>Heading</h2>', $safe);
        $this->assertStringContainsString('href="/contact"', $safe);
        $this->assertStringContainsString('<table>', $safe);
        $this->assertStringContainsString('text-align:center', $safe);
        foreach (['<script', 'onerror', 'javascript:'] as $unsafe) $this->assertStringNotContainsString($unsafe, $safe);
    }
}
