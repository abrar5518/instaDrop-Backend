<?php

namespace App\Http\Controllers\SEO;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Generate dynamic XML sitemap for InstaDrop Courier Services.
     */
    public function index(): Response
    {
        $baseUrl = rtrim(config('app.url', 'http://localhost:8000'), '/');
        $frontendUrl = rtrim(config('app.frontend_url', 'http://localhost:3000'), '/');

        $pages = [
            '/',
            '/same-day-delivery',
            '/pallet-delivery',
            '/medical-courier',
            '/legal-courier',
            '/instant-quote',
            '/business-accounts',
            '/coverage',
            '/about',
            '/contact',
            '/faq',
            '/terms-and-conditions',
            '/privacy-policy',
        ];

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

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}
