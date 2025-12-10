<?php

namespace App\Orchid\Screens\Case;

use Modules\Case\Entities\CaseModel;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;

class CaseListScreen extends Screen
{
    public function query(): array
    {
        return [
            'cases' => CaseModel::orderByDesc('updated_at')->paginate(20),
        ];
    }

    public function name(): ?string
    {
        return 'Cases';
    }

    public function commandBar(): array
    {
        return [
            Link::make('Create')->icon('bs.plus-circle')->route('platform.cases.create'),
        ];
    }

    public function layout(): array
    {
        return [
            new class extends Table {
                protected $target = 'cases';

                protected function columns(): array
                {
                    return [
                        TD::make('property_title', 'Title')
                            ->render(fn (CaseModel $case) => Link::make($case->property_title)
                                ->route('platform.cases.edit', $case)),
                        TD::make('language')->filter(TD::FILTER_SELECT, ['en' => 'en', 'de' => 'de']),
                        TD::make('category'),
                        TD::make('location'),
                        TD::make('tag'),
                        TD::make('status'),
                        TD::make('updated_at', 'Updated')
                            ->render(fn (CaseModel $case) => optional($case->updated_at)->toDateTimeString()),
                    ];
                }
            },
        ];
    }
}
