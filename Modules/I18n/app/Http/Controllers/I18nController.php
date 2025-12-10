<?php

namespace Modules\I18n\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class I18nController extends Controller
{
    public function index(Request $request, ?string $lang = null): JsonResponse
    {
        $lang = $lang ?: $request->query('lang') ?: 'en';
        $lang = in_array($lang, ['en', 'de']) ? $lang : 'en';

        $path = module_path('I18n', "resources/i18n/{$lang}.json");

        if (!File::exists($path)) {
            return response()->json(['message' => 'Translations not found'], 404);
        }

        $data = json_decode(File::get($path), true) ?: [];

        return response()->json([
            'language' => $lang,
            'data' => $data,
        ]);
    }
}
