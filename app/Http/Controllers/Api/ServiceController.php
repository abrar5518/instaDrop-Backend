<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceDirectorySetting;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::query()->published()->orderBy('sort_order')->orderBy('title')->get()
            ->map(fn (Service $service) => $service->publicData(false));

        return response()->json(['data' => $services])->header('Cache-Control', 'no-store');
    }

    public function directory()
    {
        $settings = ServiceDirectorySetting::query()->firstOrFail();

        return response()->json(['data' => $settings->publicData()])->header('Cache-Control', 'no-store');
    }

    public function show(string $slug)
    {
        $service = Service::query()->published()->where('slug', $slug)->with('sections.items')->firstOrFail();

        return response()->json(['data' => $service->publicData()])->header('Cache-Control', 'no-store');
    }
}
