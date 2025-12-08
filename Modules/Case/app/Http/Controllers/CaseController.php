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
        $query = CaseModel::query();

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

    public function show(string $slug): JsonResponse
    {
        $item = CaseModel::where('slug', $slug)->first();

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
        ]);

        $slug = $data['slug'] ?? Str::slug($data['property_title'] . '-' . Str::random(4));

        if (CaseModel::where('slug', $slug)->exists()) {
            return response()->json(['message' => 'Slug already exists'], 422);
        }

        $case = CaseModel::create(array_merge([
            'property_price' => $data['property_price'] ?? 'In production',
        ], $data, ['slug' => $slug]));

        return response()->json(['data' => $case], 201);
    }

    public function update(Request $request, string $slug): JsonResponse
    {
        $case = CaseModel::where('slug', $slug)->first();

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
}
