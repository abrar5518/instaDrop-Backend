# Blog management

Admin: `/admin/blogs` (existing admin login). Frontend: `https://instadrop.sahoolat.pk/blog`.

## Writing a blog

1. Choose **Create blog**, enter the title, slug, category and short description.
2. Upload the featured image and describe it in the alt-text field.
3. Write the entire article in the single editor, including the visible Heading 1. The title field is for cards and metadata, so it is not duplicated above the rich content.
4. Use the toolbar for headings, bold, lists, alignment, tables and image uploads. Use the editor's link button for internal page paths or external URLs.
5. Fill in the optional meta title, description and comma-separated keywords. Social title, description and image fall back to SEO fields and the featured image. Index/follow controls are available. There is no canonical field or canonical output.
6. Save as a draft or select Published and save. Only published records appear in the public API. Choosing a featured blog replaces the previous featured selection.

## Implementation

- Laravel `blogs` table, authenticated CRUD and CSRF-protected image uploads.
- Public endpoints: `GET /api/v1/blogs` and `GET /api/v1/blogs/{slug}`. Drafts/deleted records return 404 from the detail endpoint.
- Next.js reads the API without persistent caching. Refreshing/navigating to a page reflects saved content without a frontend rebuild. The approved layout, featured image, filters and related articles use this API.
- HTML is sanitized with HTML Purifier before storage; script/event-handler URLs and SVG uploads are rejected. JPEG, PNG and WebP images are limited to 5 MB.
- Featured/social/inline images use Laravel's public storage. Media is retained on deletion/replacement because rich content may reuse it.
- SEO includes metadata, Open Graph, Twitter cards and BlogPosting structured data. Published indexable articles are included in the frontend sitemap.
- Self-hosted [Jodit 4.14.2](https://xdsoft.net/jodit/docs/index.html) uses the MIT license included in `public/vendor/jodit/LICENSE.txt`. [HTML Purifier](https://htmlpurifier.org/live/configdoc/plain.html) is locked through Composer.

## Sample content and verification

`php artisan db:seed --class=BlogSeeder --force` inserts three sample articles with images. It is idempotent and does not overwrite existing article edits. Assets and content are in `database/seeders/blog-assets`.

Run `vendor/bin/phpunit --filter=BlogManagementTest` for authentication, draft/publish/update/unpublish/delete, validation, uploads and HTML sanitization. These tests use transaction rollback, not database resets, and expect the application's migrated database and an admin user/sample record. Upload tests use fake storage.

Browser verification covered editor image upload, internal linking, draft save, publishing, SEO output, unpublishing, deletion, responsive frontend layout and sitemap entries. The temporary QA article was deleted; three sample blogs remain published.
