<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceDirectorySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class ServiceController extends Controller
{
    private const SECTION_TYPES = ['content', 'steps', 'tiles', 'callout', 'faq', 'related'];

    public function index(Request $request)
    {
        $services = Service::query()->when($request->filled('search'), fn ($query) => $query->where('title', 'like', '%'.$request->string('search').'%'))
            ->orderBy('sort_order')->orderBy('title')->paginate(20)->withQueryString();

        return view('admin.services.index', compact('services'));
    }

    public function create()
    {
        return $this->form(new Service([
            'status' => 'draft', 'icon' => 'truck', 'hero_primary_label' => 'Get a quote',
            'hero_primary_url' => '/instant-quote', 'hero_secondary_label' => 'Call the 24/7 desk',
            'hero_secondary_url' => 'tel:+447852502775', 'route_kicker' => 'THE DIRECT ROUTE',
            'route_counter' => '01 / 02', 'collection_label' => 'Collection',
            'collection_detail' => 'Time confirmed by dispatch', 'delivery_label' => 'Delivery',
            'delivery_detail' => 'Recipient confirmation and POD', 'route_status' => 'DIRECT',
            'sidebar_cta_label' => 'Request a quote', 'sidebar_cta_url' => '/instant-quote',
            'bottom_cta_label' => 'Get my delivery quote', 'bottom_cta_url' => '/instant-quote',
        ]));
    }

    public function edit(Service $service)
    {
        $service->load('sections.items');

        return $this->form($service);
    }

    private function form(Service $service)
    {
        return view('admin.services.form', ['service' => $service, 'sectionTypes' => self::SECTION_TYPES]);
    }

    public function store(Request $request) { return $this->save($request, new Service); }
    public function update(Request $request, Service $service) { return $this->save($request, $service); }

    private function save(Request $request, Service $service)
    {
        $request->merge(['slug' => Str::slug($request->input('slug') ?: $request->input('title', ''))]);
        $data = $request->validate([
            'title' => 'required|string|max:255', 'navigation_title' => 'nullable|string|max:255',
            'slug' => ['required', 'string', 'max:255', Rule::unique('services')->ignore($service->id)],
            'icon' => 'required|string|max:50', 'summary' => 'nullable|string|max:2000',
            'card_badge' => 'nullable|string|max:100', 'card_subtitle' => 'nullable|string|max:500',
            'card_description' => 'nullable|string|max:3000', 'card_features_text' => 'nullable|string|max:5000',
            'card_details_text' => 'nullable|string|max:5000', 'hero_eyebrow' => 'nullable|string|max:255',
            'hero_title' => 'nullable|string|max:255', 'hero_description' => 'nullable|string|max:5000',
            'hero_primary_label' => 'nullable|string|max:100', 'hero_primary_url' => 'nullable|string|max:500',
            'hero_secondary_label' => 'nullable|string|max:100', 'hero_secondary_url' => 'nullable|string|max:500',
            'hero_points_text' => 'nullable|string|max:3000',
            'hero_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120|dimensions:max_width=8000,max_height=8000',
            'hero_image_alt' => 'nullable|string|max:255', 'existing_hero_image_path' => 'nullable|string|max:255',
            'route_kicker' => 'nullable|string|max:255', 'route_counter' => 'nullable|string|max:50',
            'route_title' => 'nullable|string|max:1000', 'collection_label' => 'nullable|string|max:255',
            'collection_detail' => 'nullable|string|max:500', 'delivery_label' => 'nullable|string|max:255',
            'delivery_detail' => 'nullable|string|max:500', 'route_footer' => 'nullable|string|max:255',
            'route_status' => 'nullable|string|max:100', 'notice_title' => 'nullable|string|max:255',
            'notice_body' => 'nullable|string|max:3000', 'sidebar_title' => 'nullable|string|max:255',
            'sidebar_body' => 'nullable|string|max:3000', 'sidebar_cta_label' => 'nullable|string|max:100',
            'sidebar_cta_url' => 'nullable|string|max:500', 'sidebar_helpful_details' => 'nullable|string|max:3000',
            'bottom_cta_title' => 'nullable|string|max:255', 'bottom_cta_body' => 'nullable|string|max:3000',
            'bottom_cta_label' => 'nullable|string|max:100', 'bottom_cta_url' => 'nullable|string|max:500',
            'status' => ['required', Rule::in(['draft', 'published'])], 'sort_order' => 'required|integer|min:0|max:10000',
            'meta_title' => 'nullable|string|max:255', 'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:1000', 'og_title' => 'nullable|string|max:255',
            'og_description' => 'nullable|string|max:500',
            'og_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120|dimensions:max_width=8000,max_height=8000',
            'existing_og_image_path' => 'nullable|string|max:255',
            'sections' => 'nullable|array|max:40', 'sections.*.section_type' => ['required', Rule::in(self::SECTION_TYPES)],
            'sections.*.anchor_id' => 'nullable|string|max:100', 'sections.*.label' => 'nullable|string|max:255',
            'sections.*.heading' => 'nullable|string|max:255', 'sections.*.intro' => 'nullable|string|max:5000',
            'sections.*.body' => 'nullable|string|max:30000', 'sections.*.callout_heading' => 'nullable|string|max:255',
            'sections.*.callout_body' => 'nullable|string|max:5000',
            'sections.*.image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120|dimensions:max_width=8000,max_height=8000',
            'sections.*.image_alt' => 'nullable|string|max:255', 'sections.*.existing_image_path' => 'nullable|string|max:255',
            'sections.*.show_in_sidebar' => 'nullable|boolean', 'sections.*.is_enabled' => 'nullable|boolean',
            'sections.*.items' => 'nullable|array|max:50', 'sections.*.items.*.badge' => 'nullable|string|max:255',
            'sections.*.items.*.title' => 'nullable|string|max:255', 'sections.*.items.*.body' => 'nullable|string|max:10000',
            'sections.*.items.*.link_label' => 'nullable|string|max:100', 'sections.*.items.*.link_url' => 'nullable|string|max:500',
            'sections.*.items.*.image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120|dimensions:max_width=8000,max_height=8000',
            'sections.*.items.*.image_alt' => 'nullable|string|max:255', 'sections.*.items.*.existing_image_path' => 'nullable|string|max:255',
        ]);
        foreach (['noindex', 'nofollow'] as $field) $data[$field] = $request->boolean($field);
        $data['card_features'] = $this->lines($data['card_features_text'] ?? null);
        $data['card_details'] = $this->pairs($data['card_details_text'] ?? null);
        $data['hero_points'] = $this->lines($data['hero_points_text'] ?? null);
        $data['published_at'] = $data['status'] === 'published' ? ($service->published_at ?? now()) : null;
        unset($data['card_features_text'], $data['card_details_text'], $data['hero_points_text']);

        $oldPaths = $service->exists ? $this->mediaPaths($service) : [];
        $allowedPaths = array_flip($oldPaths); $newPaths = []; $keptPaths = [];
        try {
            foreach (['hero_image' => 'hero_image_path', 'og_image' => 'og_image_path'] as $input => $column) {
                $existing = $data['existing_'.$input.'_path'] ?? null;
                unset($data['existing_'.$input.'_path']);
                $path = isset($allowedPaths[$existing]) ? $existing : null;
                if ($request->hasFile($input)) { $path = $request->file($input)->store('services', 'public'); $newPaths[] = $path; }
                $data[$column] = $path; if ($path) $keptPaths[] = $path;
            }
            $this->requireAlt($data['hero_image_path'], $data['hero_image_alt'] ?? null, 'hero_image_alt');
            $sections = $this->prepareSections($request, $data['sections'] ?? [], $allowedPaths, $newPaths, $keptPaths);
            unset($data['sections'], $data['hero_image'], $data['og_image']);
            DB::transaction(function () use ($service, $data, $sections) {
                $service->fill($data)->save();
                $service->sections()->delete();
                foreach ($sections as $sectionData) {
                    $items = $sectionData['items']; unset($sectionData['items']);
                    $section = $service->sections()->create($sectionData);
                    foreach ($items as $item) $section->items()->create($item);
                }
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($newPaths); throw $exception;
        }
        Storage::disk('public')->delete(array_values(array_diff($oldPaths, $keptPaths)));

        return redirect()->route('admin.services.edit', $service)->with('success', $service->status === 'published' ? 'Service published on the website.' : 'Service draft saved.');
    }

    private function prepareSections(Request $request, array $sections, array $allowedPaths, array &$newPaths, array &$keptPaths): array
    {
        $prepared = [];
        foreach ($sections as $sectionIndex => $section) {
            $items = [];
            foreach ($section['items'] ?? [] as $itemIndex => $item) {
                $existing = $item['existing_image_path'] ?? null; $path = isset($allowedPaths[$existing]) ? $existing : null;
                if ($request->hasFile("sections.$sectionIndex.items.$itemIndex.image")) { $path = $request->file("sections.$sectionIndex.items.$itemIndex.image")->store('services', 'public'); $newPaths[] = $path; }
                $this->requireAlt($path, $item['image_alt'] ?? null, "sections.$sectionIndex.items.$itemIndex.image_alt");
                if ($path) $keptPaths[] = $path;
                $itemData = array_intersect_key($item, array_flip(['badge', 'title', 'body', 'link_label', 'link_url', 'image_alt']));
                $itemData['image_path'] = $path; $itemData['sort_order'] = count($items);
                if ($this->hasContent($itemData)) $items[] = $itemData;
            }
            $existing = $section['existing_image_path'] ?? null; $path = isset($allowedPaths[$existing]) ? $existing : null;
            if ($request->hasFile("sections.$sectionIndex.image")) { $path = $request->file("sections.$sectionIndex.image")->store('services', 'public'); $newPaths[] = $path; }
            $this->requireAlt($path, $section['image_alt'] ?? null, "sections.$sectionIndex.image_alt");
            if ($path) $keptPaths[] = $path;
            $sectionData = array_intersect_key($section, array_flip(['section_type', 'anchor_id', 'label', 'heading', 'intro', 'body', 'callout_heading', 'callout_body', 'image_alt']));
            $sectionData['anchor_id'] = Str::slug($sectionData['anchor_id'] ?? $sectionData['heading'] ?? 'section-'.(count($prepared) + 1));
            $sectionData['image_path'] = $path; $sectionData['show_in_sidebar'] = (bool) ($section['show_in_sidebar'] ?? false);
            $sectionData['is_enabled'] = (bool) ($section['is_enabled'] ?? false); $sectionData['sort_order'] = count($prepared); $sectionData['items'] = $items;
            if ($this->hasContent($sectionData) || $items !== []) $prepared[] = $sectionData;
        }
        return $prepared;
    }

    private function hasContent(array $data): bool
    {
        foreach ($data as $key => $value) {
            if (in_array($key, ['section_type', 'sort_order', 'is_enabled', 'show_in_sidebar', 'items'], true)) continue;
            if (filled($value)) return true;
        }
        return false;
    }

    private function requireAlt(?string $path, ?string $alt, string $field): void
    {
        if ($path && blank($alt)) throw ValidationException::withMessages([$field => 'Alt text is required whenever an image is used.']);
    }

    private function lines(?string $value): array { return collect(preg_split('/\R/', (string) $value))->map(fn ($line) => trim($line))->filter()->values()->all(); }
    private function pairs(?string $value): array { return collect(preg_split('/\R/', (string) $value))->map(function ($line) { [$label, $detail] = array_pad(explode('|', $line, 2), 2, ''); return ['label' => trim($label), 'value' => trim($detail)]; })->filter(fn ($pair) => $pair['label'] !== '' || $pair['value'] !== '')->values()->all(); }

    private function mediaPaths(Service $service): array
    {
        $service->loadMissing('sections.items');
        return collect([$service->hero_image_path, $service->og_image_path])->merge($service->sections->pluck('image_path'))
            ->merge($service->sections->flatMap(fn ($section) => $section->items->pluck('image_path')))
            ->filter(fn ($path) => is_string($path) && (str_starts_with($path, 'services/') || str_starts_with($path, 'content-pages/')))->unique()->values()->all();
    }

    public function destroy(Service $service)
    {
        $paths = $this->mediaPaths($service); $service->delete(); Storage::disk('public')->delete($paths);
        return redirect()->route('admin.services.index')->with('success', 'Service deleted.');
    }

    public function editDirectory()
    {
        return view('admin.services.directory', ['settings' => ServiceDirectorySetting::query()->firstOrFail()]);
    }

    public function updateDirectory(Request $request)
    {
        $settings = ServiceDirectorySetting::query()->firstOrFail();
        $data = $request->validate([
            'hero_badge' => 'nullable|string|max:255', 'hero_title' => 'required|string|max:255',
            'hero_description' => 'nullable|string|max:3000', 'cta_label' => 'nullable|string|max:100',
            'cta_url' => 'nullable|string|max:500', 'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500', 'meta_keywords' => 'nullable|string|max:1000',
            'og_title' => 'nullable|string|max:255', 'og_description' => 'nullable|string|max:500',
        ]);
        $data['noindex'] = $request->boolean('noindex'); $data['nofollow'] = $request->boolean('nofollow');
        $settings->update($data);
        return back()->with('success', 'Services directory settings saved.');
    }
}
