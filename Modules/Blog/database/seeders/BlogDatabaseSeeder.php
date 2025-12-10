<?php

namespace Modules\Blog\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Modules\Blog\Entities\Post;

class BlogDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = module_path('Blog', 'resources/data/posts.json');
        if (!File::exists($path)) {
            $this->command?->warn('posts.json not found, skipping BlogDatabaseSeeder');
            return;
        }

        $payload = json_decode(File::get($path), true) ?: [];

        DB::table('post_translations')->delete();
        Post::query()->delete();
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::statement("DELETE FROM sqlite_sequence WHERE name in ('posts','post_translations')");
        }

        $targetLangs = ['en', 'de'];

        $grouped = collect($payload)
            ->filter(fn ($item) => !empty($item['slug']) && $item['slug'] !== 'string')
            ->groupBy(fn ($item) => $item['slug'] ?? Str::slug(($item['title'] ?? 'post') . '-' . Str::random(4)));

        foreach ($grouped as $slug => $items) {
            $base = $items->firstWhere('language', 'en') ?? $items->first();
            $cover = $base['cover_image'] ?? ($base['coverImage'] ?? null);

            $post = Post::create([
                'slug' => $slug,
                'title' => $base['title'] ?? 'Untitled',
                'subtitle' => $base['subtitle'] ?? null,
                'category' => $base['category'] ?? null,
                'series' => $base['series'] ?? null,
                'excerpt' => $base['excerpt'] ?? null,
                'content' => $base['content'] ?? null,
                'content_blocks' => $base['content_blocks'] ?? null,
                'cover_image' => $cover,
                'og_image' => $base['og_image'] ?? $cover,
                'author' => $base['author'] ?? 'Automaton Soft',
                'published_at' => $base['published_at'] ?? null,
                'status' => $base['status'] ?? 'published',
                'tags' => $base['tags'] ?? [],
                'reading_time' => $base['reading_time'] ?? null,
                'meta_title' => $base['meta_title'] ?? $base['title'] ?? null,
                'meta_description' => $base['meta_description'] ?? $base['excerpt'] ?? null,
                'language' => $base['language'] ?? 'en',
                'is_featured' => $base['is_featured'] ?? false,
                'canonical_url' => $base['canonical_url'] ?? null,
            ]);

            $translations = [];
            foreach ($items as $item) {
                $lang = strtolower($item['language'] ?? 'en');
                $translations[$lang] = $item;
            }

            foreach ($targetLangs as $lang) {
                $source = $translations[$lang] ?? $translations['en'] ?? $base;
                DB::table('post_translations')->insert([
                    'post_id' => $post->id,
                    'lang' => $lang,
                    'slug' => $source['slug'] ?? $slug,
                    'title' => $source['title'] ?? $post->title,
                    'subtitle' => $source['subtitle'] ?? $post->subtitle,
                    'excerpt' => $source['excerpt'] ?? $post->excerpt,
                    'content' => $source['content'] ?? $post->content,
                    'meta_title' => $source['meta_title'] ?? $post->meta_title,
                    'meta_description' => $source['meta_description'] ?? $post->meta_description,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
