<?php

namespace App\Orchid\Screens\Blog;

use Modules\Blog\Entities\Post;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;
use Illuminate\Support\Str;

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
                Input::make('post.subtitle')->title('Subtitle'),
                Input::make('post.slug')->title('Slug')->help('If empty, will be generated automatically'),
                Input::make('post.author')->title('Author'),
                Input::make('post.category')->title('Category'),
                Input::make('post.series')->title('Series'),
                Input::make('post.cover_image')->title('Cover Image URL')->placeholder('/images/cover.jpg'),
                Input::make('post.og_image')->title('OG Image URL')->placeholder('/images/cover.jpg'),
                Select::make('post.status')->title('Status')->options([
                    'draft' => 'Draft',
                    'published' => 'Published',
                ])->empty('Select status'),
                Input::make('post.published_at')->type('date')->title('Publish date'),
                Input::make('post.tags')
                    ->title('Tags')
                    ->help('Comma-separated tags')
                    ->placeholder('telecom, b2b, rbac')
                    ->value(function ($post) {
                        $tags = $post->tags ?? [];
                        if (is_array($tags)) {
                            return implode(', ', $tags);
                        }
                        return $tags;
                    }),
                Input::make('post.reading_time')->type('number')->min(0)->title('Reading time (minutes)'),
                TextArea::make('post.excerpt')->title('Excerpt')->rows(3),
                TextArea::make('post.content')->title('Content')->rows(8),
                Input::make('post.meta_title')->title('Meta title'),
                TextArea::make('post.meta_description')->title('Meta description')->rows(2),
                Input::make('post.canonical_url')->title('Canonical URL'),
                Switcher::make('post.is_featured')->title('Featured'),
                Input::make('post.language')->title('Language')->placeholder('en'),
            ]),
        ];
    }

    public function save(Post $post): void
    {
        $data = request()->validate([
            'post.title' => 'required|string',
            'post.slug' => 'nullable|string',
            'post.author' => 'nullable|string',
            'post.subtitle' => 'nullable|string',
            'post.category' => 'nullable|string',
            'post.series' => 'nullable|string',
            'post.cover_image' => 'nullable|string',
            'post.og_image' => 'nullable|string',
            'post.published_at' => 'nullable|date',
            'post.excerpt' => 'nullable|string',
            'post.content' => 'nullable|string',
            'post.status' => 'nullable|in:draft,published',
            'post.tags' => 'nullable|string',
            'post.reading_time' => 'nullable|integer|min:0',
            'post.meta_title' => 'nullable|string',
            'post.meta_description' => 'nullable|string',
            'post.canonical_url' => 'nullable|string',
            'post.is_featured' => 'nullable|boolean',
            'post.language' => 'nullable|string',
        ])['post'];

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        if (!empty($data['tags']) && is_string($data['tags'])) {
            $data['tags'] = array_values(array_filter(array_map('trim', explode(',', $data['tags']))));
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
