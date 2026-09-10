<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Blog;

class BlogController extends Controller
{
    public function index()
    {
        return response()->json(['data' => Blog::published()->orderByDesc('is_featured')->orderByDesc('published_at')->orderByDesc('id')->get()->map(fn ($blog) => $blog->publicData())])->header('Cache-Control', 'no-store');
    }

    public function show(string $slug)
    {
        return response()->json(['data' => Blog::published()->where('slug', $slug)->firstOrFail()->publicData(true)])->header('Cache-Control', 'no-store');
    }
}
