<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Services\BlogHtmlSanitizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $blogs = Blog::query()->when($request->filled('search'), fn ($q) => $q->where('title', 'like', '%'.$request->string('search').'%'))
            ->when(in_array($request->query('status'), ['draft', 'published']), fn ($q) => $q->where('status', $request->query('status')))
            ->latest()->paginate(15)->withQueryString();
        return view('admin.blogs.index', compact('blogs'));
    }

    public function create() { return $this->form(new Blog(['status' => 'draft'])); }
    public function edit(Blog $blog) { return $this->form($blog); }
    private function form(Blog $blog)
    {
        return view('admin.blogs.form', compact('blog'));
    }

    public function store(Request $request, BlogHtmlSanitizer $sanitizer) { return $this->save($request, new Blog, $sanitizer); }
    public function update(Request $request, Blog $blog, BlogHtmlSanitizer $sanitizer) { return $this->save($request, $blog, $sanitizer); }

    private function save(Request $request, Blog $blog, BlogHtmlSanitizer $sanitizer)
    {
        $request->merge(['slug' => Str::slug($request->input('slug') ?: $request->input('title', ''))]);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => ['required', 'string', 'max:255', Rule::unique('blogs')->ignore($blog->id)],
            'category' => 'required|string|max:100', 'excerpt' => 'required|string|max:1000',
            'content' => 'required|string|max:1000000', 'status' => ['required', Rule::in(['draft', 'published'])],
            'image' => [$blog->image_path ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=8000,max_height=8000'],
            'image_alt' => 'required|string|max:255',
            'meta_title' => 'nullable|string|max:255', 'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:1000', 'og_title' => 'nullable|string|max:255',
            'og_description' => 'nullable|string|max:500',
            'og_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120|dimensions:max_width=8000,max_height=8000',
        ]);
        $data['content'] = $sanitizer->clean($data['content']);
        if (trim(html_entity_decode(strip_tags($data['content']))) === '') {
            throw ValidationException::withMessages(['content' => 'Please write some article content.']);
        }
        foreach (['is_featured', 'noindex', 'nofollow'] as $field) $data[$field] = $request->boolean($field);
        $data['published_at'] = $data['status'] === 'published' ? ($blog->published_at ?? now()) : null;
        unset($data['image'], $data['og_image']);
        $newPaths = [];
        try {
            foreach (['image' => 'image_path', 'og_image' => 'og_image_path'] as $input => $column) {
                if ($request->hasFile($input)) {
                    $path = $request->file($input)->store('blogs', 'public');
                    if (!$path) throw new \RuntimeException('Image storage failed. Please try again.');
                    $data[$column] = $path;
                    $newPaths[] = $path;
                }
            }
            DB::transaction(function () use ($blog, $data) {
                if ($data['is_featured']) Blog::where('id', '!=', $blog->id ?? 0)->update(['is_featured' => false]);
                $blog->fill($data)->save();
            });
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($newPaths);
            throw $exception;
        }
        return redirect()->route('admin.blogs.edit', $blog)->with('success', $blog->status === 'published' ? 'Blog published. The website now shows your saved changes.' : 'Draft saved. It is not visible on the website.');
    }

    public function destroy(Blog $blog)
    {
        $blog->delete();
        // Keep media: the same image may be referenced inside another article.
        return redirect()->route('admin.blogs.index')->with('success', 'Blog deleted and removed from the website.');
    }

    public function upload(Request $request)
    {
        $request->validate(['files' => 'required|array|min:1|max:5', 'files.*' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120|dimensions:max_width=8000,max_height=8000']);
        $urls = [];
        foreach ($request->file('files') as $file) {
            $path = $file->store('blogs/editor', 'public');
            abort_unless($path, 500, 'Image upload failed.');
            $urls[] = Storage::disk('public')->url($path);
        }
        return response()->json(['success' => true, 'data' => ['files' => $urls, 'isImages' => array_fill(0, count($urls), true), 'baseurl' => '', 'path' => '', 'messages' => []]]);
    }
}
