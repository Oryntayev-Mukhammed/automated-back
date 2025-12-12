<?php

namespace Modules\Case\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Case\Entities\CaseModel;
use Modules\Case\Entities\CaseTranslation;

class CaseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $lang = $this->resolveLanguage($request);
        $fallbackLang = 'en';
        $query = CaseModel::query()->with(['translations' => function ($q) use ($lang, $fallbackLang) {
            $q->whereIn('lang', [$lang, $fallbackLang]);
        }]);

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
            $query->whereHas('translations', function ($q) use ($status, $lang, $fallbackLang) {
                $q->whereIn('lang', [$lang, $fallbackLang])->where('status', $status);
            });
        }

        $perPage = min(max((int)$request->query('per_page', 10), 1), 100);
        $cases = $query->paginate($perPage);

        $cases->setCollection($cases->getCollection()->map(function (CaseModel $case) use ($lang, $fallbackLang) {
            $translation = $case->translations->firstWhere('lang', $lang)
                ?? $case->translations->firstWhere('lang', $fallbackLang);

            $title = $translation->title ?? $translation->property_title ?? $case->title ?? $case->property_title;
            $domain = $translation->domain ?? $translation->property_price ?? $case->domain ?? $case->property_price;
            $summary = $translation->summary ?? $translation->livingArea ?? $case->summary ?? $case->livingArea;
            $cover = $translation->cover_image ?? $case->cover_image;

            return array_merge($case->toArray(), [
                'lang' => $translation->lang ?? $case->language ?? $fallbackLang,
                'slug' => $translation->slug ?? $case->slug,
                // new names
                'title' => $title,
                'domain' => $domain,
                'summary' => $summary,
                'cover_image' => $cover,
                // legacy keys for compatibility
                'property_title' => $title,
                'property_price' => $domain,
                'livingArea' => $summary,
                'location' => $translation->location ?? $case->location,
                'tag' => $translation->tag ?? $case->tag,
                'status' => $translation->status ?? $case->status,
                'type' => $translation->type ?? $case->type,
            ]);
        }));

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
        $fallbackLang = 'en';
        $translation = CaseTranslation::with('case')
            ->where('slug', $slug)
            ->where('lang', $lang)
            ->first();

        if (!$translation && $lang !== $fallbackLang) {
            $translation = CaseTranslation::with('case')
                ->where('slug', $slug)
                ->where('lang', $fallbackLang)
                ->first();
        }

        if ($translation && $translation->case) {
            $case = $translation->case;
            $title = $translation->title ?? $translation->property_title ?? $case->title ?? $case->property_title;
            $domain = $translation->domain ?? $translation->property_price ?? $case->domain ?? $case->property_price;
            $summary = $translation->summary ?? $translation->livingArea ?? $case->summary ?? $case->livingArea;
            $cover = $translation->cover_image ?? $case->cover_image;

            return response()->json([
                'data' => array_merge($case->toArray(), [
                    'lang' => $translation->lang,
                    'slug' => $translation->slug,
                    'title' => $title,
                    'domain' => $domain,
                    'summary' => $summary,
                    'cover_image' => $cover,
                    'property_title' => $title,
                    'property_price' => $domain,
                    'livingArea' => $summary,
                    'location' => $translation->location,
                    'tag' => $translation->tag,
                    'status' => $translation->status,
                    'type' => $translation->type,
                ]),
            ]);
        }

        // legacy fallback
        $legacy = CaseModel::where('slug', $slug)->first();
        if ($legacy) {
            return response()->json(['data' => $legacy]);
        }

        return response()->json(['message' => 'Case not found'], 404);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'slug' => 'nullable|string',
            'cover_image' => 'nullable|string',
            'category' => 'nullable|string',
            'type' => 'nullable|string',
            'translations' => 'required|array',
            'translations.*.lang' => 'required|string|in:en,de',
            'translations.*.title' => 'required_without:translations.*.property_title|string|nullable',
            'translations.*.property_title' => 'nullable|string',
            'translations.*.slug' => 'nullable|string',
            'translations.*.domain' => 'nullable|string',
            'translations.*.property_price' => 'nullable|string',
            'translations.*.location' => 'nullable|string',
            'translations.*.summary' => 'nullable|string',
            'translations.*.livingArea' => 'nullable|string',
            'translations.*.tag' => 'nullable|string',
            'translations.*.status' => 'nullable|string',
            'translations.*.type' => 'nullable|string',
            'translations.*.cover_image' => 'nullable|string',
        ]);

        $translations = $data['translations'];
        $baseTitle = $translations[0]['title'] ?? $translations[0]['property_title'] ?? 'case';
        $baseSlug = $data['slug'] ?? Str::slug($baseTitle . '-' . Str::random(4));

        $case = CaseModel::create([
            'title' => $translations[0]['title'] ?? $translations[0]['property_title'] ?? null,
            'property_title' => $translations[0]['title'] ?? $translations[0]['property_title'] ?? null,
            'cover_image' => $data['cover_image'] ?? null,
            'domain' => $translations[0]['domain'] ?? $translations[0]['property_price'] ?? null,
            'property_price' => $translations[0]['domain'] ?? $translations[0]['property_price'] ?? null,
            'category' => $data['category'] ?? null,
            'location' => $translations[0]['location'] ?? null,
            'summary' => $translations[0]['summary'] ?? $translations[0]['livingArea'] ?? null,
            'livingArea' => $translations[0]['summary'] ?? $translations[0]['livingArea'] ?? null,
            'tag' => $translations[0]['tag'] ?? null,
            'status' => $translations[0]['status'] ?? null,
            'type' => $translations[0]['type'] ?? $data['type'] ?? null,
            'slug' => $baseSlug,
            'language' => $translations[0]['lang'],
        ]);

        foreach ($translations as $translation) {
            $langCode = $this->normalizeLanguage($translation['lang']);
            $tSlug = $translation['slug'] ?? $baseSlug;
            $tTitle = $translation['title'] ?? $translation['property_title'] ?? $case->title;
            $tDomain = $translation['domain'] ?? $translation['property_price'] ?? $case->domain;
            $tSummary = $translation['summary'] ?? $translation['livingArea'] ?? $case->summary;
            $tCover = $translation['cover_image'] ?? $case->cover_image;

            CaseTranslation::create([
                'case_id' => $case->id,
                'lang' => $langCode,
                'slug' => $tSlug,
                'title' => $tTitle,
                'property_title' => $tTitle,
                'domain' => $tDomain,
                'property_price' => $tDomain,
                'location' => $translation['location'] ?? $case->location,
                'summary' => $tSummary,
                'livingArea' => $tSummary,
                'tag' => $translation['tag'] ?? $case->tag,
                'status' => $translation['status'] ?? $case->status,
                'type' => $translation['type'] ?? $case->type,
                'cover_image' => $tCover,
            ]);
        }

        return response()->json(['data' => $case->fresh('translations')], 201);
    }

    public function update(Request $request, string $slug): JsonResponse
    {
        $lang = $this->resolveLanguage($request);
        $translation = CaseTranslation::where('slug', $slug)->where('lang', $lang)->first();
        $case = $translation?->case ?? CaseModel::where('slug', $slug)->first();

        if (!$case) {
            return response()->json(['message' => 'Case not found'], 404);
        }

        $basePayload = $request->only([
            'cover_image', 'category', 'type',
        ]);
        $mappedBase = [
            'cover_image' => $basePayload['cover_image'] ?? null,
            'category' => $basePayload['category'] ?? null,
            'type' => $basePayload['type'] ?? null,
        ];
        $case->fill(array_filter($mappedBase, fn($v) => !is_null($v)));
        $case->save();

        $tPayload = $request->only([
            'title', 'property_title', 'domain', 'property_price', 'summary', 'livingArea', 'location', 'tag', 'status', 'type', 'slug', 'cover_image',
        ]);

        if (!empty(array_filter($tPayload, fn($v) => !is_null($v)))) {
            if (!$translation) {
                $translation = new CaseTranslation([
                    'case_id' => $case->id,
                    'lang' => $lang,
                ]);
            }
            $title = $tPayload['title'] ?? $tPayload['property_title'] ?? null;
            $domain = $tPayload['domain'] ?? $tPayload['property_price'] ?? null;
            $summary = $tPayload['summary'] ?? $tPayload['livingArea'] ?? null;
            $cover = $tPayload['cover_image'] ?? null;

            if ($title !== null) {
                $translation->title = $title;
                $translation->property_title = $title;
            }
            if ($domain !== null) {
                $translation->domain = $domain;
                $translation->property_price = $domain;
            }
            if ($summary !== null) {
                $translation->summary = $summary;
                $translation->livingArea = $summary;
            }
            if ($cover !== null) {
                $translation->cover_image = $cover;
            }
            foreach (['location', 'tag', 'status', 'type', 'slug'] as $key) {
                if (!is_null($tPayload[$key] ?? null)) {
                    $translation->{$key} = $tPayload[$key];
                }
            }
            if (!$translation->slug) {
                $translation->slug = $slug;
            }
            $translation->save();
        }

        return response()->json(['data' => $case->fresh('translations')]);
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

    private function normalizeLanguage(?string $lang): string
    {
        if (!$lang || strlen($lang) < 2) {
            return 'en';
        }
        $lang = substr($lang, 0, 2);
        return in_array($lang, ['en', 'de']) ? $lang : 'en';
    }
}
