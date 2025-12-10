<?php

namespace App\Orchid\Screens\Blog;

use Modules\Blog\Entities\Post;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;

class PostListScreen extends Screen
{
    public function query(): array
    {
        return [
            'posts' => Post::orderByDesc('published_at')->orderByDesc('updated_at')->paginate(20),
        ];
    }

    public function name(): ?string
    {
        return 'Posts';
    }

    public function commandBar(): array
    {
        return [
            Link::make('Create')->icon('bs.plus-circle')->route('platform.posts.create'),
        ];
    }

    public function layout(): array
    {
        return [
            new class extends Table {
                protected $target = 'posts';

                protected function columns(): array
                {
                    return [
                        TD::make('title')
                            ->filter(TD::FILTER_TEXT)
                            ->render(fn (Post $post) => Link::make($post->title)->route('platform.posts.edit', $post)),
                        TD::make('slug')->filter(TD::FILTER_TEXT),
                        TD::make('language')->filter(TD::FILTER_SELECT, ['en' => 'en', 'de' => 'de']),
                        TD::make('category')->filter(TD::FILTER_TEXT),
                        TD::make('author')->filter(TD::FILTER_TEXT),
                        TD::make('status')->filter(TD::FILTER_SELECT, [
                            'draft' => 'Draft',
                            'published' => 'Published',
                        ]),
                        TD::make('cover_image', 'Cover')->render(function (Post $post) {
                            return $post->cover_image
                                ? Link::make('View')->href($post->cover_image)->target('_blank')
                                : '—';
                        }),
                        TD::make('published_at')->render(fn (Post $post) => optional($post->published_at)->toDateString()),
                        TD::make('updated_at', 'Updated')->render(fn (Post $post) => optional($post->updated_at)->toDateTimeString()),
                    ];
                }
            },
        ];
    }
}
