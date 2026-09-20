<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class ContentPageController extends Controller
{
    private const SECTION_TYPES = ['rich_text', 'feature_grid', 'steps', 'info_cards', 'comparison', 'region_grid', 'faq', 'cta'];

    public function index(Request $request)
    {
        $pages = ContentPage::query()
            ->when($request->filled('search'), fn ($query) => $query->where('title', 'like', '%'.$request->string('search').'%'))
            ->orderByRaw("CASE page_type WHEN 'services_index' THEN 0 WHEN 'coverage' THEN 1 ELSE 2 END")
            ->orderBy('sort_order')->orderBy('title')->paginate(20)->withQueryString();

        return view('admin.content-pages.index', compact('pages'));
    }

    public function create()
    {
        return $this->form(new ContentPage([
            'page_type' => 'service', 'status' => 'draft', 'icon' => 'truck',
            'primary_cta_label' => 'Get Speedy Quote', 'primary_cta_url' => '/instant-quote',
        ]));
    }

    public function edit(ContentPage $contentPage)
    {
        $contentPage->load('sections.items');

        return $this->form($contentPage);
    }

    private function form(ContentPage $contentPage)
    {
        return view('admin.content-pages.form', ['page' => $contentPage, 'sectionTypes' => self::SECTION_TYPES]);
    }

    public function store(Request $request)
    {
        return $this->save($request, new ContentPage);
    }

    public function update(Request $request, ContentPage $contentPage)
    {
        return $this->save($request, $contentPage);
    }

    private function save(Request $request, ContentPage $page)
    {
        $pageType = $page->exists ? $page->page_type : 'service';
        $request->merge([
            'page_type' => $pageType,
            'slug' => Str::slug($request->input('slug') ?: $request->input('title', '')),
        ]);
        $data = $request->validate([
            'page_type' => ['required', Rule::in(['service', 'services_index', 'coverage'])],
            'title' => 'required|string|max:255',
            'navigation_title' => 'nullable|string|max:255',
            'slug' => ['required', 'string', 'max:255', Rule::unique('content_pages')->ignore($page->id)],
            'icon' => 'required|string|max:50', 'summary' => 'nullable|string|max:2000',
            'hero_badge' => 'nullable|string|max:255', 'hero_title' => 'nullable|string|max:255',
            'hero_description' => 'nullable|string|max:3000',
            'hero_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120|dimensions:max_width=8000,max_height=8000',
            'hero_image_alt' => 'nullable|string|max:255', 'existing_hero_image_path' => 'nullable|string|max:255',
            'primary_cta_label' => 'nullable|string|max:100', 'primary_cta_url' => 'nullable|string|max:500',
            'card_badge' => 'nullable|string|max:100', 'card_subtitle' => 'nullable|string|max:500',
            'card_description' => 'nullable|string|max:3000',
            'card_features_text' => 'nullable|string|max:5000', 'card_details_text' => 'nullable|string|max:5000',
            'card_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120|dimensions:max_width=8000,max_height=8000',
            'card_image_alt' => 'nullable|string|max:255', 'existing_card_image_path' => 'nullable|string|max:255',
            'status' => ['required', Rule::in(['draft', 'published'])], 'sort_order' => 'required|integer|min:0|max:10000',
            'meta_title' => 'nullable|string|max:255', 'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:1000', 'og_title' => 'nullable|string|max:255',
            'og_description' => 'nullable|string|max:500',
            'og_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120|dimensions:max_width=8000,max_height=8000',
            'existing_og_image_path' => 'nullable|string|max:255',
            'sections' => 'nullable|array|max:30',
            'sections.*.section_type' => ['required', Rule::in(self::SECTION_TYPES)],
            'sections.*.eyebrow' => 'nullable|string|max:255', 'sections.*.heading' => 'nullable|string|max:255',
            'sections.*.body' => 'nullable|string|max:10000',
            'sections.*.image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120|dimensions:max_width=8000,max_height=8000',
            'sections.*.image_alt' => 'nullable|string|max:255', 'sections.*.existing_image_path' => 'nullable|string|max:255',
            'sections.*.cta_label' => 'nullable|string|max:100', 'sections.*.cta_url' => 'nullable|string|max:500',
            'sections.*.theme' => ['required', Rule::in(['light', 'soft', 'dark'])],
            'sections.*.is_enabled' => 'nullable|boolean', 'sections.*.items' => 'nullable|array|max:50',
            'sections.*.items.*.title' => 'nullable|string|max:255',
            'sections.*.items.*.subtitle' => 'nullable|string|max:500',
            'sections.*.items.*.description' => 'nullable|string|max:5000',
            'sections.*.items.*.badge' => 'nullable|string|max:255',
            'sections.*.items.*.value' => 'nullable|string|max:1000',
            'sections.*.items.*.icon' => 'nullable|string|max:50',
            'sections.*.items.*.image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120|dimensions:max_width=8000,max_height=8000',
            'sections.*.items.*.image_alt' => 'nullable|string|max:255',
            'sections.*.items.*.existing_image_path' => 'nullable|string|max:255',
            'sections.*.items.*.link_label' => 'nullable|string|max:100',
            'sections.*.items.*.link_url' => 'nullable|string|max:500',
        ]);
        foreach (['noindex', 'nofollow'] as $field) {
            $data[$field] = $request->boolean($field);
        }
        $data['card_features'] = $this->lines($data['card_features_text'] ?? null);
        $data['card_details'] = $this->pairs($data['card_details_text'] ?? null);
        $data['published_at'] = $data['status'] === 'published' ? ($page->published_at ?? now()) : null;
        unset($data['card_features_text'], $data['card_details_text']);

        $oldPaths = $page->exists ? $this->mediaPaths($page) : [];
        $allowedPaths = array_flip($oldPaths);
        $newPaths = [];
        $keptPaths = [];

        try {
            foreach (['hero_image' => 'hero_image_path', 'card_image' => 'card_image_path', 'og_image' => 'og_image_path'] as $input => $column) {
                $existing = $data['existing_'.$input.'_path'] ?? null;
                unset($data['existing_'.$input.'_path']);
                $path = isset($allowedPaths[$existing]) ? $existing : null;
                if ($request->hasFile($input)) {
                    $path = $request->file($input)->store('content-pages', 'public');
                    $newPaths[] = $path;
                }
                $data[$column] = $path;
                if ($path) $keptPaths[] = $path;
            }
            $this->requireAlt($data['hero_image_path'], $data['hero_image_alt'] ?? null, 'hero_image_alt');
            $this->requireAlt($data['card_image_path'], $data['card_image_alt'] ?? null, 'card_image_alt');

            $sections = $this->prepareSections($request, $data['sections'] ?? [], $allowedPaths, $newPaths, $keptPaths);
            unset($data['sections'], $data['hero_image'], $data['card_image'], $data['og_image']);

            DB::transaction(function () use ($page, $data, $sections) {
                $page->fill($data)->save();
                $page->sections()->delete();
                foreach ($sections as $sectionData) {
                    $items = $sectionData['items'];
                    unset($sectionData['items']);
                    $section = $page->sections()->create($sectionData);
                    foreach ($items as $item) $section->items()->create($item);
                }
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($newPaths);
            throw $exception;
        }

        Storage::disk('public')->delete(array_values(array_diff($oldPaths, $keptPaths)));

        return redirect()->route('admin.content-pages.edit', $page)
            ->with('success', $page->status === 'published' ? 'Page published. The website now shows the saved content.' : 'Draft saved. It is not visible on the website.');
    }

    private function prepareSections(Request $request, array $sections, array $allowedPaths, array &$newPaths, array &$keptPaths): array
    {
        $prepared = [];
        foreach ($sections as $sectionIndex => $section) {
            $items = [];
            foreach ($section['items'] ?? [] as $itemIndex => $item) {
                $existing = $item['existing_image_path'] ?? null;
                $path = isset($allowedPaths[$existing]) ? $existing : null;
                if ($request->hasFile("sections.$sectionIndex.items.$itemIndex.image")) {
                    $path = $request->file("sections.$sectionIndex.items.$itemIndex.image")->store('content-pages', 'public');
                    $newPaths[] = $path;
                }
                $this->requireAlt($path, $item['image_alt'] ?? null, "sections.$sectionIndex.items.$itemIndex.image_alt");
                if ($path) $keptPaths[] = $path;
                $itemData = array_intersect_key($item, array_flip(['title', 'subtitle', 'description', 'badge', 'value', 'icon', 'image_alt', 'link_label', 'link_url']));
                $itemData['image_path'] = $path;
                $itemData['sort_order'] = count($items);
                if ($this->hasContent($itemData)) $items[] = $itemData;
            }
            $existing = $section['existing_image_path'] ?? null;
            $path = isset($allowedPaths[$existing]) ? $existing : null;
            if ($request->hasFile("sections.$sectionIndex.image")) {
                $path = $request->file("sections.$sectionIndex.image")->store('content-pages', 'public');
                $newPaths[] = $path;
            }
            $this->requireAlt($path, $section['image_alt'] ?? null, "sections.$sectionIndex.image_alt");
            if ($path) $keptPaths[] = $path;
            $sectionData = array_intersect_key($section, array_flip(['section_type', 'eyebrow', 'heading', 'body', 'image_alt', 'cta_label', 'cta_url', 'theme']));
            $sectionData['image_path'] = $path;
            $sectionData['sort_order'] = count($prepared);
            $sectionData['is_enabled'] = (bool) ($section['is_enabled'] ?? false);
            $sectionData['items'] = $items;
            if ($this->hasContent($sectionData) || $items !== []) $prepared[] = $sectionData;
        }
        return $prepared;
    }

    private function hasContent(array $data): bool
    {
        foreach ($data as $key => $value) {
            if (in_array($key, ['section_type', 'theme', 'sort_order', 'is_enabled', 'items'], true)) continue;
            if (filled($value)) return true;
        }
        return false;
    }

    private function requireAlt(?string $path, ?string $alt, string $field): void
    {
        if ($path && blank($alt)) throw ValidationException::withMessages([$field => 'Alt text is required whenever an image is used.']);
    }

    private function lines(?string $value): array
    {
        return collect(preg_split('/\R/', (string) $value))->map(fn ($line) => trim($line))->filter()->values()->all();
    }

    private function pairs(?string $value): array
    {
        return collect(preg_split('/\R/', (string) $value))->map(function ($line) {
            [$label, $detail] = array_pad(explode('|', $line, 2), 2, '');
            return ['label' => trim($label), 'value' => trim($detail)];
        })->filter(fn ($pair) => $pair['label'] !== '' || $pair['value'] !== '')->values()->all();
    }

    private function mediaPaths(ContentPage $page): array
    {
        $page->loadMissing('sections.items');
        return collect([$page->hero_image_path, $page->card_image_path, $page->og_image_path])
            ->merge($page->sections->pluck('image_path'))
            ->merge($page->sections->flatMap(fn ($section) => $section->items->pluck('image_path')))
            ->filter(fn ($path) => is_string($path) && str_starts_with($path, 'content-pages/'))->unique()->values()->all();
    }

    public function destroy(ContentPage $contentPage)
    {
        abort_unless($contentPage->page_type === 'service', 403, 'Core pages cannot be deleted.');
        $paths = $this->mediaPaths($contentPage);
        $contentPage->delete();
        Storage::disk('public')->delete($paths);

        return redirect()->route('admin.content-pages.index')->with('success', 'Service deleted and removed from the website.');
    }
}
