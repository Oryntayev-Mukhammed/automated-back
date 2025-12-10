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

            return array_merge($case->toArray(), [
                'lang' => $translation->lang ?? $case->language ?? $fallbackLang,
                'slug' => $translation->slug ?? $case->slug,
                'property_title' => $translation->property_title ?? $case->property_title,
                'property_price' => $translation->property_price ?? $case->property_price,
                'location' => $translation->location ?? $case->location,
                'livingArea' => $translation->livingArea ?? $case->livingArea,
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
            return response()->json([
                'data' => array_merge($case->toArray(), [
                    'lang' => $translation->lang,
                    'slug' => $translation->slug,
                    'property_title' => $translation->property_title,
                    'property_price' => $translation->property_price,
                    'location' => $translation->location,
                    'livingArea' => $translation->livingArea,
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
            'property_img' => 'nullable|string',
            'category' => 'nullable|string',
            'type' => 'nullable|string',
            'translations' => 'required|array',
            'translations.*.lang' => 'required|string|in:en,de',
            'translations.*.property_title' => 'required|string',
            'translations.*.slug' => 'nullable|string',
            'translations.*.property_price' => 'nullable|string',
            'translations.*.location' => 'nullable|string',
            'translations.*.livingArea' => 'nullable|string',
            'translations.*.tag' => 'nullable|string',
            'translations.*.status' => 'nullable|string',
            'translations.*.type' => 'nullable|string',
        ]);

        $translations = $data['translations'];
        $baseSlug = $data['slug'] ?? Str::slug(($translations[0]['property_title'] ?? 'case') . '-' . Str::random(4));

        $case = CaseModel::create([
            'property_title' => $translations[0]['property_title'],
            'property_img' => $data['property_img'] ?? null,
            'property_price' => $translations[0]['property_price'] ?? null,
            'category' => $data['category'] ?? null,
            'location' => $translations[0]['location'] ?? null,
            'livingArea' => $translations[0]['livingArea'] ?? null,
            'tag' => $translations[0]['tag'] ?? null,
            'status' => $translations[0]['status'] ?? null,
            'type' => $translations[0]['type'] ?? $data['type'] ?? null,
            'slug' => $baseSlug,
            'language' => $translations[0]['lang'],
        ]);

        foreach ($translations as $translation) {
            $langCode = $this->normalizeLanguage($translation['lang']);
            $tSlug = $translation['slug'] ?? $baseSlug;

            CaseTranslation::create([
                'case_id' => $case->id,
                'lang' => $langCode,
                'slug' => $tSlug,
                'property_title' => $translation['property_title'] ?? $case->property_title,
                'property_price' => $translation['property_price'] ?? $case->property_price,
                'location' => $translation['location'] ?? $case->location,
                'livingArea' => $translation['livingArea'] ?? $case->livingArea,
                'tag' => $translation['tag'] ?? $case->tag,
                'status' => $translation['status'] ?? $case->status,
                'type' => $translation['type'] ?? $case->type,
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
            'property_img', 'category', 'type',
        ]);
        $case->fill(array_filter($basePayload, fn($v) => !is_null($v)));
        $case->save();

        $tPayload = $request->only([
            'property_title', 'property_price', 'location', 'livingArea', 'tag', 'status', 'type', 'slug'
        ]);

        if (!empty(array_filter($tPayload, fn($v) => !is_null($v)))) {
            if (!$translation) {
                $translation = new CaseTranslation([
                    'case_id' => $case->id,
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
