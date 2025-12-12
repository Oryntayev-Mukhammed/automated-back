<?php

namespace App\Orchid\Screens\Case;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Orchid\Attachment\Models\Attachment;
use Modules\Case\Entities\CaseModel;
use Modules\Case\Entities\CaseTranslation;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Label;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\Upload;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class CaseEditScreen extends Screen
{
    public $exists = false;

    public function query(CaseModel $case): array
    {
        $this->exists = $case->exists;

        $translations = $case->exists
            ? $case->translations()->get()->keyBy('lang')->map(function ($item) {
                $arr = $item->toArray();
                $arr['title'] = $arr['title'] ?? $arr['property_title'] ?? '';
                $arr['domain'] = $arr['domain'] ?? $arr['property_price'] ?? '';
                $arr['summary'] = $arr['summary'] ?? $arr['livingArea'] ?? '';
                return $arr;
            })->toArray()
            : [];

        return [
            'case' => array_merge($case->toArray(), [
                'cover_image' => $case->cover_image,
                'title' => $case->title ?? $case->property_title,
                'domain' => $case->domain ?? $case->property_price,
                'summary' => $case->summary ?? $case->livingArea,
            ]),
            'translations' => $translations,
        ];
    }

    public function name(): ?string
    {
        return $this->exists ? 'Edit Case' : 'Create Case';
    }

    public function commandBar(): array
    {
        return [
            Button::make('Save')->icon('bs.check')->method('save'),
            Button::make('Delete')->icon('bs.trash')->method('remove')->canSee($this->exists),
        ];
    }

    public function layout(): array
    {
        return [
            Layout::rows([
                Group::make([
                    Input::make('case.cover_image')->title('Cover image URL'),
                    Input::make('case.category')->title('Category'),
                    Input::make('case.type')->title('Type'),
                ])->autoWidth(),
                Upload::make('cover')
                    ->title('Upload cover')
                    ->maxFiles(1)
                    ->acceptedFiles('image/*')
                    ->groups('cases'),
                Input::make('case.slug')->title('Base slug')->help('Used as fallback if translation slug is empty'),
            ]),
            Layout::tabs([
                'English' => Layout::rows($this->translationFields('en')),
                'Deutsch' => Layout::rows($this->translationFields('de')),
            ]),
        ];
    }

    private function translationFields(string $lang): array
    {
        $prefix = "translations.$lang.";
        return [
            Input::make($prefix . 'title')->title("Title ($lang)")->required($lang === 'en'),
            Input::make($prefix . 'slug')->title("Slug ($lang)")->help('If empty, will be generated automatically'),
            Input::make($prefix . 'domain')->title("Domain / Endpoint ($lang)"),
            Input::make($prefix . 'location')->title("Location ($lang)"),
            TextArea::make($prefix . 'summary')->title("Stack / Description ($lang)")->rows(3),
            Input::make($prefix . 'tag')->title("Tag ($lang)"),
            Input::make($prefix . 'status')->title("Status ($lang)"),
            Input::make($prefix . 'type')->title("Type ($lang)"),
        ];
    }

    public function save(CaseModel $case, Request $request): void
    {
        $validated = $request->validate([
            'case.slug' => 'nullable|string',
            'case.cover_image' => 'nullable|string',
            'case.category' => 'nullable|string',
            'case.type' => 'nullable|string',
            'translations' => 'required|array',
            'translations.*' => 'array',
            'translations.en.title' => 'required_without:translations.en.property_title|string|nullable',
            'translations.*.title' => 'nullable|string',
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
        ]);

        $baseData = $validated['case'] ?? [];
        if (isset($baseData['cover_image'])) {
            // keep only cover_image
        }
        $case->fill($baseData);

        $coverIds = $request->input('cover', []);
        if (!empty($coverIds)) {
            $attachment = Attachment::find($coverIds[0]);
            if ($attachment) {
                $case->cover_image = $attachment->url();
            }
        }

        $translations = $validated['translations'] ?? [];
        // Ensure required base title/property_title for DB not-null constraints
        $primaryTitle = $translations['en']['title'] ?? $translations['en']['property_title'] ?? null;
        if (!$case->property_title && $primaryTitle) {
            $case->property_title = $primaryTitle;
        }
        if (!$case->title && $primaryTitle) {
            $case->title = $primaryTitle;
        }
        // Fallback slug/language with uniqueness
        $langBase = $case->language ?? 'en';
        $baseSlug = $case->slug ?: ($primaryTitle ? Str::slug($primaryTitle) : null);
        $case->language = $langBase;
        if ($baseSlug) {
            $case->slug = $this->uniqueCaseSlug($baseSlug, $langBase, $case->id);
        }

        $case->save();

        foreach (['en', 'de'] as $lang) {
            $tData = $translations[$lang] ?? [];
            $title = $tData['title'] ?? $tData['property_title'] ?? null;
            if (empty($title)) {
                continue;
            }
            $slug = $tData['slug'] ?? ($case->slug ?: Str::slug($title));
            $translation = CaseTranslation::firstOrNew([
                'case_id' => $case->id,
                'lang' => $lang,
            ]);
            $translation->fill([
                'slug' => $slug,
                'title' => $title,
                'property_title' => $title,
                'domain' => $tData['domain'] ?? $tData['property_price'] ?? null,
                'property_price' => $tData['domain'] ?? $tData['property_price'] ?? null,
                'location' => $tData['location'] ?? null,
                'summary' => $tData['summary'] ?? $tData['livingArea'] ?? null,
                'livingArea' => $tData['summary'] ?? $tData['livingArea'] ?? null,
                'tag' => $tData['tag'] ?? null,
                'status' => $tData['status'] ?? null,
                'type' => $tData['type'] ?? $case->type,
                'cover_image' => $tData['cover_image'] ?? $case->cover_image,
            ]);
            $translation->save();
        }

        Alert::info('Case saved');
    }

    public function remove(CaseModel $case): void
    {
        $case->delete();
        Alert::info('Case deleted');
    }

    private function uniqueCaseSlug(string $baseSlug, string $lang, ?int $ignoreId = null): string
    {
        $slug = $baseSlug;
        $counter = 1;
        while (CaseModel::where('slug', $slug)->where('language', $lang)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
        return $slug;
    }
}
