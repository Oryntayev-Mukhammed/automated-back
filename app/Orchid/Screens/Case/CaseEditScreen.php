<?php

namespace App\Orchid\Screens\Case;

use Modules\Case\Entities\CaseModel;
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

        return [
            'case' => $case,
        ];
    }

    public function name(): ?string
    {
        return $this->exists ? 'Edit Case' : 'Create Case';
    }

    public function commandBar(): array
    {
        return [
            Button::make('Save')
                ->icon('bs.check')
                ->method('save'),

            Button::make('Delete')
                ->icon('bs.trash')
                ->method('remove')
                ->canSee($this->exists),
        ];
    }

    public function layout(): array
    {
        return [
            Layout::rows([
                Input::make('case.property_title')->title('Title')->required(),
                Input::make('case.slug')->title('Slug')->help('If empty, will be generated automatically'),
                Group::make([
                    Select::make('case.language')
                        ->title('Language')
                        ->options(['en' => 'English', 'de' => 'Deutsch'])
                        ->required()
                        ->empty('Select language'),
                    Input::make('case.property_img')->title('Image URL'),
                ])->autoWidth(),
                Input::make('case.property_price')->title('Status/Price')->placeholder('In production'),
                Input::make('case.category')->title('Category'),
                Input::make('case.type')->title('Type'),
                Input::make('case.tag')->title('Tag'),
                Input::make('case.status')->title('Status'),
                Input::make('case.location')->title('Location'),
                TextArea::make('case.livingArea')->title('Stack / Description')->rows(3),
            ]),
        ];
    }

    public function save(CaseModel $case): void
    {
        $data = request()->validate([
            'case.property_title' => 'required|string',
            'case.slug' => 'nullable|string',
            'case.property_img' => 'nullable|string',
            'case.property_price' => 'nullable|string',
            'case.category' => 'nullable|string',
            'case.type' => 'nullable|string',
            'case.tag' => 'nullable|string',
            'case.status' => 'nullable|string',
            'case.location' => 'nullable|string',
            'case.livingArea' => 'nullable|string',
            'case.language' => 'required|string|in:en,de',
        ])['case'];

        if (empty($data['slug'])) {
            $data['slug'] = \Str::slug($data['property_title']);
        }

        $case->fill($data)->save();

        Alert::info('Case saved');
    }

    public function remove(CaseModel $case): void
    {
        $case->delete();
        Alert::info('Case deleted');
    }
}
