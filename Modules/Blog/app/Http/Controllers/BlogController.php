<?php

namespace Modules\Blog\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Blog\Entities\Post;

class BlogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Post::query();

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        $perPage = min(max((int)$request->query('per_page', 10), 1), 100);
        $posts = $query->orderByDesc('published_at')->paginate($perPage);

        return response()->json([
            'data' => $posts->items(),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
                'last_page' => $posts->lastPage(),
            ],
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $item = Post::where('slug', $slug)->first();

        if (!$item) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        return response()->json(['data' => $item]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'slug' => 'nullable|string',
            'title' => 'required|string',
            'excerpt' => 'nullable|string',
            'coverImage' => 'nullable|string',
            'author' => 'nullable|string',
            'published_at' => 'nullable|date',
        ]);

        $slug = $data['slug'] ?? Str::slug($data['title'] . '-' . Str::random(4));

        if (Post::where('slug', $slug)->exists()) {
            return response()->json(['message' => 'Slug already exists'], 422);
        }

        $post = Post::create(array_merge([
            'slug' => $slug,
            'author' => $data['author'] ?? 'Automaton Soft',
        ], $data));

        return response()->json(['data' => $post], 201);
    }

    public function update(Request $request, string $slug): JsonResponse
    {
        $post = Post::where('slug', $slug)->first();

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $payload = $request->only(['title', 'excerpt', 'coverImage', 'author', 'published_at']);
        $post->fill(array_filter($payload, fn($v) => !is_null($v)));
        $post->save();

        return response()->json(['data' => $post]);
    }

    public function destroy(string $slug): JsonResponse
    {
        $post = Post::where('slug', $slug)->first();

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $post->delete();
        return response()->json(['message' => 'Post deleted']);
    }
}
