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
            'cases' => CaseModel::with('translations')->orderByDesc('updated_at')->paginate(20),
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
                        TD::make('title_en', 'Title (en)')
                            ->render(fn (CaseModel $case) => Link::make(optional($case->translations->firstWhere('lang', 'en'))->property_title ?? $case->property_title ?? '—')
                                ->route('platform.cases.edit', $case)),
                        TD::make('title_de', 'Title (de)')
                            ->render(fn (CaseModel $case) => optional($case->translations->firstWhere('lang', 'de'))->property_title ?? '—'),
                        TD::make('slug', 'Slug (en)')
                            ->render(fn (CaseModel $case) => optional($case->translations->firstWhere('lang', 'en'))->slug ?? $case->slug ?? '—'),
                        TD::make('slug_de', 'Slug (de)')
                            ->render(fn (CaseModel $case) => optional($case->translations->firstWhere('lang', 'de'))->slug ?? '—'),
                        TD::make('category'),
                        TD::make('type'),
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
