<?php

namespace App\Orchid\Screens\Blog;

use Modules\Blog\Entities\Post;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class PostEditScreen extends Screen
{
    public $exists = false;

    public function query(Post $post): array
    {
        $this->exists = $post->exists;

        return [
            'post' => $post,
        ];
    }

    public function name(): ?string
    {
        return $this->exists ? 'Edit Post' : 'Create Post';
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
                Input::make('post.title')->title('Title')->required(),
                Input::make('post.slug')->title('Slug')->help('If empty, will be generated automatically'),
                Input::make('post.author')->title('Author'),
                Input::make('post.coverImage')->title('Cover Image'),
                Input::make('post.published_at')->type('date')->title('Publish date'),
                TextArea::make('post.excerpt')->title('Excerpt')->rows(3),
            ]),
        ];
    }

    public function save(Post $post): void
    {
        $data = request()->validate([
            'post.title' => 'required|string',
            'post.slug' => 'nullable|string',
            'post.author' => 'nullable|string',
            'post.coverImage' => 'nullable|string',
            'post.published_at' => 'nullable|date',
            'post.excerpt' => 'nullable|string',
        ])['post'];

        if (empty($data['slug'])) {
            $data['slug'] = \Str::slug($data['title']);
        }

        $post->fill($data)->save();

        Alert::info('Post saved');
    }

    public function remove(Post $post): void
    {
        $post->delete();
        Alert::info('Post deleted');
    }
}
