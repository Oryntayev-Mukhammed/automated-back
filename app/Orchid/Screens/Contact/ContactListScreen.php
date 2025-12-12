<?php

namespace App\Orchid\Screens\Contact;

use Modules\Contact\Entities\ContactSubmission;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;

class ContactListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'submissions' => ContactSubmission::latest()->paginate(25),
        ];
    }

    public function name(): ?string
    {
        return 'Contact submissions';
    }

    public function commandBar(): array
    {
        return [];
    }

    public function layout(): iterable
    {
        return [
            new class extends Table {
                protected $target = 'submissions';

                protected function columns(): array
                {
                    return [
                        TD::make('id')->sort(),
                        TD::make('email')->render(fn ($s) => e($s->email)),
                        TD::make('first_name', 'First name')->render(fn ($s) => e($s->first_name)),
                        TD::make('last_name', 'Last name')->render(fn ($s) => e($s->last_name)),
                        TD::make('specialist')->render(fn ($s) => e($s->specialist)),
                        TD::make('status')->render(fn ($s) => e($s->status)),
                        TD::make('created_at', 'Created')->render(fn ($s) => $s->created_at?->toDateTimeString()),
                    ];
                }
            },
        ];
    }
}
