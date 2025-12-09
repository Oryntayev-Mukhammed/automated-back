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
            'posts' => Post::paginate(20),
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
                            ->render(fn (Post $post) => Link::make($post->title)->route('platform.posts.edit', $post)),
                        TD::make('slug'),
                        TD::make('author'),
                        TD::make('status'),
                        TD::make('cover_image', 'Cover')->render(fn (Post $post) => $post->cover_image
                            ? Link::make('View')->href($post->cover_image)->target('_blank')
                            : '—'),
                        TD::make('published_at')->render(fn (Post $post) => optional($post->published_at)->toDateString()),
                        TD::make('updated_at', 'Updated')->render(fn (Post $post) => optional($post->updated_at)->toDateTimeString()),
                    ];
                }
            },
        ];
    }
}
