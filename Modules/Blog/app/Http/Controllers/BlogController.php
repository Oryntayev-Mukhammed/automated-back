<?php

namespace Modules\Blog\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Blog\Entities\Post;
use Modules\Blog\Entities\PostTranslation;

class BlogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $lang = $this->resolveLanguage($request);
        $fallbackLang = 'en';
        $query = Post::query()->with(['translations' => function ($q) use ($lang, $fallbackLang) {
            $q->whereIn('lang', [$lang, $fallbackLang]);
        }]);

        if ($search = $request->query('search')) {
            $query->whereHas('translations', function ($q) use ($search, $lang, $fallbackLang) {
                $q->whereIn('lang', [$lang, $fallbackLang])
                    ->where(function ($inner) use ($search) {
                        $inner->where('title', 'like', "%{$search}%")
                            ->orWhere('excerpt', 'like', "%{$search}%");
                    });
            });
        }

        $perPage = min(max((int)$request->query('per_page', 10), 1), 100);
        $posts = $query->orderByDesc('published_at')->paginate($perPage);

        $posts->setCollection($posts->getCollection()->map(function (Post $post) use ($lang, $fallbackLang) {
            $translation = $post->translations->firstWhere('lang', $lang)
                ?? $post->translations->firstWhere('lang', $fallbackLang);

            return array_merge($post->toArray(), [
                'lang' => $translation->lang ?? $post->language ?? $fallbackLang,
                'slug' => $translation->slug ?? $post->slug,
                'title' => $translation->title ?? $post->title,
                'subtitle' => $translation->subtitle ?? $post->subtitle,
                'excerpt' => $translation->excerpt ?? $post->excerpt,
                'content' => $translation->content ?? $post->content,
                'meta_title' => $translation->meta_title ?? $post->meta_title,
                'meta_description' => $translation->meta_description ?? $post->meta_description,
            ]);
        }));

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

    public function show(string $slug, Request $request): JsonResponse
    {
        $lang = $this->resolveLanguage($request);
        $fallbackLang = 'en';

        $translation = PostTranslation::with('post')
            ->where('slug', $slug)
            ->where('lang', $lang)
            ->first();

        if (!$translation && $lang !== $fallbackLang) {
            $translation = PostTranslation::with('post')
                ->where('slug', $slug)
                ->where('lang', $fallbackLang)
                ->first();
        }

        if ($translation && $translation->post) {
            $post = $translation->post;
            return response()->json([
                'data' => array_merge($post->toArray(), [
                    'lang' => $translation->lang,
                    'slug' => $translation->slug,
                    'title' => $translation->title,
                    'subtitle' => $translation->subtitle,
                    'excerpt' => $translation->excerpt,
                    'content' => $translation->content,
                    'meta_title' => $translation->meta_title,
                    'meta_description' => $translation->meta_description,
                ])
            ]);
        }

        // legacy fallback: look up in posts table directly
        $legacy = Post::where('slug', $slug)->first();
        if ($legacy) {
            return response()->json(['data' => $legacy]);
        }

        return response()->json(['message' => 'Post not found'], 404);
    }

    public function store(Request $request): JsonResponse
    {
        $baseData = $request->validate([
            'slug' => 'nullable|string',
            'cover_image' => 'nullable|string',
            'og_image' => 'nullable|string',
            'author' => 'nullable|string',
            'published_at' => 'nullable|date',
            'status' => 'nullable|in:draft,published',
            'tags' => 'nullable|array',
            'content_blocks' => 'nullable|array',
            'reading_time' => 'nullable|integer|min:0',
            'language' => 'nullable|string',
            'is_featured' => 'nullable|boolean',
            'canonical_url' => 'nullable|string',
            'translations' => 'nullable|array',
            'translations.*.lang' => 'required_with:translations|string',
            'translations.*.slug' => 'nullable|string',
            'translations.*.title' => 'required_with:translations|string',
            'translations.*.subtitle' => 'nullable|string',
            'translations.*.excerpt' => 'nullable|string',
            'translations.*.content' => 'nullable|string',
            'translations.*.meta_title' => 'nullable|string',
            'translations.*.meta_description' => 'nullable|string',
        ]);

        $language = $baseData['language'] ?? 'en';
        $slug = $baseData['slug'] ?? Str::slug(($baseData['translations'][0]['title'] ?? 'post') . '-' . Str::random(4));

        $post = Post::create(array_merge([
            'slug' => $slug,
            'author' => $baseData['author'] ?? 'Automaton Soft',
            'status' => $baseData['status'] ?? 'draft',
            'language' => $language,
            'is_featured' => $baseData['is_featured'] ?? false,
        ], $baseData));

        $translations = $baseData['translations'] ?? [];
        if (empty($translations) && $request->input('title')) {
            $translations[] = [
                'lang' => $language,
                'slug' => $slug,
                'title' => $request->input('title'),
                'subtitle' => $request->input('subtitle'),
                'excerpt' => $request->input('excerpt'),
                'content' => $request->input('content'),
                'meta_title' => $request->input('meta_title'),
                'meta_description' => $request->input('meta_description'),
            ];
        }

        foreach ($translations as $translation) {
            $langCode = $this->normalizeLanguage($translation['lang'] ?? $language);
            $tSlug = $translation['slug'] ?? $slug;

            if (PostTranslation::where('slug', $tSlug)->where('lang', $langCode)->exists()) {
                return response()->json(['message' => "Slug already exists for language {$langCode}"], 422);
            }

            PostTranslation::create([
                'post_id' => $post->id,
                'lang' => $langCode,
                'slug' => $tSlug,
                'title' => $translation['title'] ?? '',
                'subtitle' => $translation['subtitle'] ?? null,
                'excerpt' => $translation['excerpt'] ?? null,
                'content' => $translation['content'] ?? null,
                'meta_title' => $translation['meta_title'] ?? null,
                'meta_description' => $translation['meta_description'] ?? null,
            ]);
        }

        return response()->json(['data' => $post], 201);
    }

    public function update(Request $request, string $slug): JsonResponse
    {
        $lang = $this->resolveLanguage($request);
        $translation = PostTranslation::where('slug', $slug)->where('lang', $lang)->first();
        $post = $translation?->post ?? Post::where('slug', $slug)->first();

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $basePayload = $request->only([
            'cover_image',
            'og_image',
            'author',
            'published_at',
            'status',
            'tags',
            'content_blocks',
            'reading_time',
            'language',
            'is_featured',
            'canonical_url',
        ]);
        $post->fill(array_filter($basePayload, fn($v) => !is_null($v)));
        $post->save();

        $tPayload = $request->only([
            'title',
            'subtitle',
            'excerpt',
            'content',
            'meta_title',
            'meta_description',
            'slug',
        ]);

        if (!empty(array_filter($tPayload, fn($v) => !is_null($v)))) {
            if (!$translation) {
                $translation = new PostTranslation([
                    'post_id' => $post->id,
                    'lang' => $lang,
                ]);
            }
            foreach ($tPayload as $key => $value) {
                if (!is_null($value)) {
                    $translation->{$key} = $value;
                }
            }
            if (!$translation->slug) {
                $translation->slug = $slug;
            }
            $translation->save();
        }

        return response()->json(['data' => $post->fresh('translations')]);
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

    private function normalizeLanguage(?string $lang): string
    {
        if (!$lang || strlen($lang) < 2) {
            return 'en';
        }
        $lang = substr($lang, 0, 2);
        return in_array($lang, ['en', 'de']) ? $lang : 'en';
    }
}
