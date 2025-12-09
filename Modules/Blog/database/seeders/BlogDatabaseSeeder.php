<?php

namespace Modules\Blog\Database\Seeders;

use Illuminate\Database\Seeder;
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
        $rows = collect($payload)->filter(fn ($item) => !empty($item['slug']) && $item['slug'] !== 'string')->map(function ($item) {
            $slug = $item['slug'] ?? Str::slug(($item['title'] ?? 'post') . '-' . Str::random(4));
            $cover = $item['cover_image'] ?? ($item['coverImage'] ?? null);
            return [
                'slug' => $slug,
                'title' => $item['title'] ?? 'Untitled',
                'subtitle' => $item['subtitle'] ?? null,
                'category' => $item['category'] ?? null,
                'series' => $item['series'] ?? null,
                'excerpt' => $item['excerpt'] ?? null,
                'content' => $item['content'] ?? null,
                'content_blocks' => $item['content_blocks'] ?? null,
                'cover_image' => $cover,
                'og_image' => $item['og_image'] ?? $cover,
                'author' => $item['author'] ?? 'Automaton Soft',
                'published_at' => $item['published_at'] ?? null,
                'status' => $item['status'] ?? 'published',
                'tags' => $item['tags'] ?? [],
                'reading_time' => $item['reading_time'] ?? null,
                'meta_title' => $item['meta_title'] ?? $item['title'] ?? null,
                'meta_description' => $item['meta_description'] ?? $item['excerpt'] ?? null,
                'language' => $item['language'] ?? 'en',
                'is_featured' => $item['is_featured'] ?? false,
                'canonical_url' => $item['canonical_url'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->all();

        Post::query()->truncate();
        Post::query()->insert($rows);
    }
}
