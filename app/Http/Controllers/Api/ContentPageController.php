<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContentPage;

class ContentPageController extends Controller
{
    public function services()
    {
        $services = ContentPage::query()->published()->where('page_type', 'service')
            ->orderBy('sort_order')->orderBy('title')->get()->map(fn (ContentPage $page) => $page->publicData(false));

        return response()->json(['data' => $services])->header('Cache-Control', 'no-store');
    }

    public function service(string $slug)
    {
        $service = ContentPage::query()->published()->where('page_type', 'service')
            ->where('slug', $slug)->with('sections.items')->firstOrFail();

        return response()->json(['data' => $service->publicData()])->header('Cache-Control', 'no-store');
    }

    public function managedPage(string $slug)
    {
        abort_unless(in_array($slug, ['services', 'coverage'], true), 404);
        $page = ContentPage::query()->published()->where('slug', $slug)
            ->whereIn('page_type', ['services_index', 'coverage'])->with('sections.items')->firstOrFail();

        return response()->json(['data' => $page->publicData()])->header('Cache-Control', 'no-store');
    }
}
