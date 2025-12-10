<?php

namespace Modules\Case\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Case\Entities\CaseModel;

class CaseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $lang = $this->resolveLanguage($request);
        $query = CaseModel::query()->where('language', $lang);

        if ($category = $request->query('category')) {
            $query->where('category', $category);
        }
        if ($location = $request->query('location')) {
            $query->where('location', $location);
        }
        if ($tag = $request->query('tag')) {
            $query->where('tag', $tag);
        }
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $perPage = min(max((int)$request->query('per_page', 10), 1), 100);
        $cases = $query->paginate($perPage);

        return response()->json([
            'data' => $cases->items(),
            'meta' => [
                'current_page' => $cases->currentPage(),
                'per_page' => $cases->perPage(),
                'total' => $cases->total(),
                'last_page' => $cases->lastPage(),
            ],
        ]);
    }

    public function show(string $slug, Request $request): JsonResponse
    {
        $lang = $this->resolveLanguage($request);
        $item = CaseModel::where('slug', $slug)->where('language', $lang)->first();

        if (!$item && $lang !== 'en') {
            $item = CaseModel::where('slug', $slug)->where('language', 'en')->first();
        }

        if (!$item) {
            return response()->json(['message' => 'Case not found'], 404);
        }

        return response()->json(['data' => $item]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'property_title' => 'required|string',
            'property_img' => 'nullable|string',
            'category' => 'nullable|string',
            'location' => 'nullable|string',
            'livingArea' => 'nullable|string',
            'tag' => 'nullable|string',
            'status' => 'nullable|string',
            'type' => 'nullable|string',
            'slug' => 'nullable|string',
            'language' => 'nullable|string|in:en,de',
        ]);

        $slug = $data['slug'] ?? Str::slug($data['property_title'] . '-' . Str::random(4));
        $language = $data['language'] ?? 'en';

        if (CaseModel::where('slug', $slug)->where('language', $language)->exists()) {
            return response()->json(['message' => 'Slug already exists for this language'], 422);
        }

        $case = CaseModel::create(array_merge([
            'property_price' => $data['property_price'] ?? 'In production',
        ], $data, ['slug' => $slug, 'language' => $language]));

        return response()->json(['data' => $case], 201);
    }

    public function update(Request $request, string $slug): JsonResponse
    {
        $lang = $this->resolveLanguage($request);
        $case = CaseModel::where('slug', $slug)->where('language', $lang)->first();

        if (!$case) {
            return response()->json(['message' => 'Case not found'], 404);
        }

        $payload = $request->only([
            'property_title', 'property_img', 'property_price', 'category',
            'location', 'livingArea', 'tag', 'status', 'type'
        ]);

        $case->fill(array_filter($payload, fn($v) => !is_null($v)));
        $case->save();

        return response()->json(['data' => $case]);
    }

    public function destroy(string $slug): JsonResponse
    {
        $case = CaseModel::where('slug', $slug)->first();

        if (!$case) {
            return response()->json(['message' => 'Case not found'], 404);
        }

        $case->delete();
        return response()->json(['message' => 'Case deleted']);
    }

    private function resolveLanguage(Request $request): string
    {
        $lang = $request->query('lang')
            ?? $request->header('X-Lang')
            ?? $request->header('Accept-Language');

        if (is_string($lang) && strlen($lang) >= 2) {
            $lang = substr($lang, 0, 2);
        } else {
            $lang = 'en';
        }

        return in_array($lang, ['en', 'de']) ? $lang : 'en';
    }
}
