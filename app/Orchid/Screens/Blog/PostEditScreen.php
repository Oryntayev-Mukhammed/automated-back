<?php

namespace App\Orchid\Screens\Blog;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Blog\Entities\Post;
use Modules\Blog\Entities\PostTranslation;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\Quill;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class PostEditScreen extends Screen
{
    public $exists = false;

    public function query(Post $post): array
    {
        $this->exists = $post->exists;

        $translations = $post->exists
            ? $post->translations()->get()->keyBy('lang')->toArray()
            : [];

        return [
            'post' => $post,
            'translations' => $translations,
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
                Group::make([
                    Select::make('post.status')
                        ->title('Status')
                        ->options([
                            'draft' => 'Draft',
                            'published' => 'Published',
                        ])
                        ->empty('Select status'),
                    Input::make('post.published_at')->type('date')->title('Publish date'),
                    Switcher::make('post.is_featured')->title('Featured'),
                ])->autoWidth(),

                Group::make([
                    Input::make('post.cover_image')->title('Cover Image URL')->placeholder('/images/cover.jpg'),
                    Input::make('post.og_image')->title('OG Image URL')->placeholder('/images/cover.jpg'),
                    Input::make('post.canonical_url')->title('Canonical URL'),
                ])->autoWidth(),

                Group::make([
                    Input::make('post.author')->title('Author'),
                    Input::make('post.category')->title('Category'),
                    Input::make('post.series')->title('Series'),
                    Input::make('post.reading_time')->type('number')->min(0)->title('Reading time (minutes)'),
                ])->autoWidth(),

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
            Input::make($prefix . 'slug')->title("Slug ($lang)")->help('If empty, will be generated automatically from title'),
            Input::make($prefix . 'subtitle')->title("Subtitle ($lang)"),
            Input::make($prefix . 'meta_title')->title("Meta title ($lang)"),
            TextArea::make($prefix . 'meta_description')->title("Meta description ($lang)")->rows(2),
            TextArea::make($prefix . 'excerpt')->title("Excerpt ($lang)")->rows(3),
            Quill::make($prefix . 'content')->title("Content ($lang)"),
        ];
    }

    public function save(Post $post, Request $request): void
    {
        $validated = $request->validate([
            'post.status' => 'nullable|in:draft,published',
            'post.published_at' => 'nullable|date',
            'post.cover_image' => 'nullable|string',
            'post.og_image' => 'nullable|string',
            'post.author' => 'nullable|string',
            'post.category' => 'nullable|string',
            'post.series' => 'nullable|string',
            'post.tags' => 'nullable|string',
            'post.reading_time' => 'nullable|integer|min:0',
            'post.is_featured' => 'nullable|boolean',
            'post.canonical_url' => 'nullable|string',
            'translations' => 'required|array',
            'translations.en.title' => 'required|string',
            'translations.de.title' => 'nullable|string',
        ]);

        $baseData = $validated['post'] ?? [];
        if (!empty($baseData['tags']) && is_string($baseData['tags'])) {
            $baseData['tags'] = array_values(array_filter(array_map('trim', explode(',', $baseData['tags']))));
        }

        $post->fill($baseData);
        $post->save();

        $translations = $validated['translations'] ?? [];
        foreach (['en', 'de'] as $lang) {
            $tData = $translations[$lang] ?? [];
            if (empty($tData['title'])) {
                continue;
            }
            $slug = $tData['slug'] ?? Str::slug($tData['title']);
            $translation = PostTranslation::firstOrNew([
                'post_id' => $post->id,
                'lang' => $lang,
            ]);
            $translation->fill([
                'slug' => $slug,
                'title' => $tData['title'],
                'subtitle' => $tData['subtitle'] ?? null,
                'excerpt' => $tData['excerpt'] ?? null,
                'content' => $tData['content'] ?? null,
                'meta_title' => $tData['meta_title'] ?? null,
                'meta_description' => $tData['meta_description'] ?? null,
            ]);
            $translation->save();
        }

        Alert::info('Post saved');
    }

    public function remove(Post $post): void
    {
        $post->delete();
        Alert::info('Post deleted');
    }
}
