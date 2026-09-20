<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CoveragePage;

class CoverageController extends Controller
{
    public function show()
    {
        $page = CoveragePage::query()->published()->with(['regions', 'sections'])->firstOrFail();

        return response()->json(['data' => $page->publicData()])->header('Cache-Control', 'no-store');
    }

    public function legacy()
    {
        $page = CoveragePage::query()->published()->with(['regions', 'sections'])->firstOrFail();
        $data = $page->publicData();
        $data['sections'] = collect([[
            'type' => 'region_grid', 'eyebrow' => $data['regionsEyebrow'], 'heading' => $data['regionsHeading'],
            'body' => null, 'image' => null, 'imageAlt' => null, 'ctaLabel' => null, 'ctaUrl' => null,
            'theme' => 'light', 'items' => collect($data['regions'])->map(fn ($region) => [
                'title' => $region['title'], 'subtitle' => null, 'description' => $region['hubs'],
                'badge' => $region['timing'], 'value' => $region['postcodes'], 'icon' => $region['icon'],
                'image' => null, 'imageAlt' => null, 'linkLabel' => null, 'linkUrl' => null, 'metadata' => null,
            ])->all(),
        ]])->merge(collect($data['sections'])->map(fn ($section) => [
            'type' => 'cta', 'eyebrow' => $section['eyebrow'], 'heading' => $section['heading'],
            'body' => $section['body'], 'image' => null, 'imageAlt' => null, 'ctaLabel' => $section['ctaLabel'],
            'ctaUrl' => $section['ctaUrl'], 'theme' => $section['theme'], 'items' => [],
        ]))->values();
        unset($data['regions'], $data['regionsEyebrow'], $data['regionsHeading'], $data['bottomCta']);

        return response()->json(['data' => $data])->header('Cache-Control', 'no-store');
    }
}
