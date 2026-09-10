<?php

namespace Database\Seeders;

use App\Models\Blog;
use App\Services\BlogHtmlSanitizer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        $samples = json_decode(file_get_contents(__DIR__.'/blog-assets/blogs.json'), true, 512, JSON_THROW_ON_ERROR);
        foreach ($samples as $index => $sample) {
            // Re-running never overwrites edits made by an administrator.
            if (Blog::where('slug', $sample['slug'])->exists()) continue;
            $bytes = file_get_contents(__DIR__.'/blog-assets/'.basename($sample['image_path']));
            if (!Storage::disk('public')->put($sample['image_path'], $bytes)) throw new \RuntimeException('Could not store sample image.');
            $sample['content'] = app(BlogHtmlSanitizer::class)->clean($sample['content']);
            $sample['published_at'] = now()->subDays($index);
            if (Blog::where('is_featured', true)->exists()) $sample['is_featured'] = false;
            Blog::create($sample);
        }
    }
}
