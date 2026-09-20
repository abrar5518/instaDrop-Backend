<?php

namespace App\Http\Controllers\SEO;

use App\Http\Controllers\Controller;
use App\Models\ContentPage;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Generate dynamic XML sitemap for InstaDrop Courier Services.
     */
    public function index(): Response
    {
        $frontendUrl = rtrim(config('app.frontend_url', 'http://localhost:3000'), '/');

        $pages = [
            '/',
            '/services',
            '/instant-quote',
            '/business-accounts',
            '/coverage',
            '/about',
            '/contact',
            '/faq',
            '/terms-and-conditions',
            '/privacy-policy',
        ];

        $servicePages = ContentPage::query()->published()->where('page_type', 'service')
            ->where('noindex', false)->orderBy('sort_order')->get(['slug', 'updated_at'])
            ->map(fn (ContentPage $page) => [
                'path' => '/'.$page->slug,
                'lastmod' => $page->updated_at?->toAtomString() ?? now()->toAtomString(),
            ]);

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($pages as $page) {
            $loc = $page === '/' ? $frontendUrl : "{$frontendUrl}{$page}";
            $priority = $page === '/' ? '1.0' : ($page === '/instant-quote' ? '0.9' : '0.8');

            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . htmlspecialchars($loc) . '</loc>' . "\n";
            $xml .= '    <lastmod>' . now()->toAtomString() . '</lastmod>' . "\n";
            $xml .= '    <changefreq>weekly</changefreq>' . "\n";
            $xml .= '    <priority>' . $priority . '</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }

        foreach ($servicePages as $page) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . htmlspecialchars($frontendUrl.$page['path']) . '</loc>' . "\n";
            $xml .= '    <lastmod>' . $page['lastmod'] . '</lastmod>' . "\n";
            $xml .= '    <changefreq>weekly</changefreq>' . "\n";
            $xml .= '    <priority>0.9</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}
