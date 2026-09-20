<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CoveragePage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CoverageController extends Controller
{
    public function edit()
    {
        $page = CoveragePage::query()->with(['regions', 'sections'])->firstOrFail();

        return view('admin.coverage.edit', compact('page'));
    }

    public function update(Request $request)
    {
        $page = CoveragePage::query()->firstOrFail();
        $data = $request->validate([
            'hero_badge' => 'nullable|string|max:255', 'hero_title' => 'required|string|max:255',
            'hero_description' => 'nullable|string|max:3000', 'regions_eyebrow' => 'nullable|string|max:255',
            'regions_heading' => 'nullable|string|max:255', 'bottom_cta_title' => 'nullable|string|max:255',
            'bottom_cta_body' => 'nullable|string|max:3000', 'bottom_cta_label' => 'nullable|string|max:100',
            'bottom_cta_url' => 'nullable|string|max:500', 'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500', 'meta_keywords' => 'nullable|string|max:1000',
            'og_title' => 'nullable|string|max:255', 'og_description' => 'nullable|string|max:500',
            'status' => ['required', Rule::in(['draft', 'published'])], 'regions' => 'nullable|array|max:100',
            'regions.*.title' => 'nullable|string|max:255', 'regions.*.timing' => 'nullable|string|max:255',
            'regions.*.hubs' => 'nullable|string|max:5000', 'regions.*.postcodes' => 'nullable|string|max:5000',
            'regions.*.icon' => 'nullable|string|max:50', 'regions.*.is_enabled' => 'nullable|boolean',
            'sections' => 'nullable|array|max:20', 'sections.*.eyebrow' => 'nullable|string|max:255',
            'sections.*.heading' => 'nullable|string|max:255', 'sections.*.body' => 'nullable|string|max:5000',
            'sections.*.cta_label' => 'nullable|string|max:100', 'sections.*.cta_url' => 'nullable|string|max:500',
            'sections.*.theme' => ['required', Rule::in(['light', 'soft', 'dark'])],
            'sections.*.is_enabled' => 'nullable|boolean',
        ]);
        foreach (['noindex', 'nofollow'] as $field) $data[$field] = $request->boolean($field);
        $data['published_at'] = $data['status'] === 'published' ? ($page->published_at ?? now()) : null;
        $regions = collect($data['regions'] ?? [])->filter(fn ($region) => filled($region['title'] ?? null))->values()->map(function ($region, $index) {
            return array_merge(array_intersect_key($region, array_flip(['title', 'timing', 'hubs', 'postcodes', 'icon'])), [
                'icon' => $region['icon'] ?: 'building', 'is_enabled' => (bool) ($region['is_enabled'] ?? false), 'sort_order' => $index,
            ]);
        })->all();
        $sections = collect($data['sections'] ?? [])->filter(fn ($section) => collect($section)->except(['theme', 'is_enabled'])->contains(fn ($value) => filled($value)))->values()->map(function ($section, $index) {
            return array_merge(array_intersect_key($section, array_flip(['eyebrow', 'heading', 'body', 'cta_label', 'cta_url', 'theme'])), [
                'is_enabled' => (bool) ($section['is_enabled'] ?? false), 'sort_order' => $index,
            ]);
        })->all();
        unset($data['regions'], $data['sections']);
        DB::transaction(function () use ($page, $data, $regions, $sections) {
            $page->update($data); $page->regions()->delete(); $page->sections()->delete();
            foreach ($regions as $region) $page->regions()->create($region);
            foreach ($sections as $section) $page->sections()->create($section);
        });

        return back()->with('success', $page->status === 'published' ? 'Coverage page published.' : 'Coverage draft saved.');
    }
}
