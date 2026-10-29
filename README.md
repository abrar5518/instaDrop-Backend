# InstaDrop — Backend & Admin Panel

Laravel backend and admin panel for **InstaDrop**, a same-day courier and logistics service. It powers the customer website (Next.js frontend) with a public REST API, and gives the operations team an admin panel for content, services and site settings.

**Live site:** [instadrop.uk](https://instadrop.uk) · **Frontend repo:** [abrar5518/instaDrop](https://github.com/abrar5518/instaDrop)

## Tech stack

- **Laravel 12** on **PHP 8.2**
- **Laravel Sanctum** for API authentication
- **HTML Purifier** — all rich-text content is sanitised before storage
- **Jodit** rich-text editor in the admin panel
- Vite + Tailwind CSS for admin assets
- PHPUnit test suite

## What it does

### Public REST API (`/api/v1`)
- `GET /settings` and `GET /settings/public` — site settings consumed by the frontend
- `GET /services`, `GET /services/{slug}`, `GET /services-page` — service directory and detail pages
- `GET /coverage` — service coverage information
- `POST /inquiries` and `POST /quotes` — customer enquiries and quote requests (rate-limited)
- `GET /tracking/{tracking_number}` — public parcel tracking lookup
- `GET /invoices/{token}` — invoice lookup for payment
- `POST /payments/paypal/create` and `POST /payments/paypal/capture` — PayPal payment flow

### Blog CMS
- Admin management at `/admin/blogs` with draft/publish workflow, categories, slugs and featured posts
- Rich-text editing with image uploads (JPEG/PNG/WebP, stored on Laravel's public disk)
- Per-post SEO controls: meta title/description/keywords, Open Graph and Twitter cards, `BlogPosting` structured data, index/follow controls
- Public read API: `GET /api/v1/blogs` and `GET /api/v1/blogs/{slug}` (drafts return 404)
- See [`BLOG_MANAGEMENT.md`](BLOG_MANAGEMENT.md) for the full editorial guide

### WhatsApp notifications
- Meta WhatsApp Cloud API integration that sends admin alerts for new enquiries, new orders and received payments using approved utility templates
- See [`WHATSAPP_AND_SEO_INTEGRATION.md`](WHATSAPP_AND_SEO_INTEGRATION.md)

### Dynamic SEO & analytics endpoints
- `GET /robots.txt`, `GET /sitemap.xml` and `GET /llms.txt` are generated dynamically from admin settings
- `GET /api/v1/analytics-settings` exposes the active analytics configuration (Meta Pixel, GA4, GTM, Clarity, Search Console) to the frontend

## Getting started

```bash
composer install
cp .env.example .env
php artisan key:generate
# configure your database in .env, then:
php artisan migrate
php artisan serve
```

## Testing

```bash
php artisan test
# Blog module only:
vendor/bin/phpunit --filter=BlogManagementTest
```

## Documentation in this repo

- [`BACKEND_EXECUTION_PLAN.md`](BACKEND_EXECUTION_PLAN.md) — build plan and architecture notes
- [`FRONTEND_DOCUMENTATION.md`](FRONTEND_DOCUMENTATION.md) — how the Next.js frontend consumes this API
- [`BLOG_MANAGEMENT.md`](BLOG_MANAGEMENT.md) — blog CMS guide
- [`WHATSAPP_INTEGRATION.md`](WHATSAPP_INTEGRATION.md) / [`WHATSAPP_AND_SEO_INTEGRATION.md`](WHATSAPP_AND_SEO_INTEGRATION.md) — WhatsApp, SEO and analytics integration guides

---

Built by [Abrar Hussain](https://github.com/abrar5518) — Senior Full Stack Developer (Next.js · Laravel · Node.js).
