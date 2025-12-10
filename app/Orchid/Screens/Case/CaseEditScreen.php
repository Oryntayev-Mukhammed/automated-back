<?php

namespace App\Orchid\Screens\Case;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Case\Entities\CaseModel;
use Modules\Case\Entities\CaseTranslation;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
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
            ? $case->translations()->get()->keyBy('lang')->toArray()
            : [];

        return [
            'case' => $case,
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
                    Input::make('case.property_img')->title('Image URL'),
                    Input::make('case.category')->title('Category'),
                    Input::make('case.type')->title('Type'),
                ])->autoWidth(),
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
            Input::make($prefix . 'property_title')->title("Title ($lang)")->required($lang === 'en'),
            Input::make($prefix . 'slug')->title("Slug ($lang)")->help('If empty, will be generated automatically'),
            Input::make($prefix . 'property_price')->title("Status/Price ($lang)"),
            Input::make($prefix . 'location')->title("Location ($lang)"),
            TextArea::make($prefix . 'livingArea')->title("Stack / Description ($lang)")->rows(3),
            Input::make($prefix . 'tag')->title("Tag ($lang)"),
            Input::make($prefix . 'status')->title("Status ($lang)"),
            Input::make($prefix . 'type')->title("Type ($lang)"),
        ];
    }

    public function save(CaseModel $case, Request $request): void
    {
        $validated = $request->validate([
            'case.slug' => 'nullable|string',
            'case.property_img' => 'nullable|string',
            'case.category' => 'nullable|string',
            'case.type' => 'nullable|string',
            'translations' => 'required|array',
            'translations.en.property_title' => 'required|string',
        ]);

        $baseData = $validated['case'] ?? [];

        $case->fill($baseData);
        $case->save();

        $translations = $validated['translations'] ?? [];
        foreach (['en', 'de'] as $lang) {
            $tData = $translations[$lang] ?? [];
            if (empty($tData['property_title'])) {
                continue;
            }
            $slug = $tData['slug'] ?? ($case->slug ?: Str::slug($tData['property_title']));
            $translation = CaseTranslation::firstOrNew([
                'case_id' => $case->id,
                'lang' => $lang,
            ]);
            $translation->fill([
                'slug' => $slug,
                'property_title' => $tData['property_title'],
                'property_price' => $tData['property_price'] ?? null,
                'location' => $tData['location'] ?? null,
                'livingArea' => $tData['livingArea'] ?? null,
                'tag' => $tData['tag'] ?? null,
                'status' => $tData['status'] ?? null,
                'type' => $tData['type'] ?? $case->type,
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
}
