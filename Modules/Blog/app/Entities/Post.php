<?php

namespace Modules\Blog\Entities;

use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use AsSource;

    protected $table = 'posts';

    protected $fillable = [
        'slug',
        'title',
        'excerpt',
        'content',
        'cover_image',
        'og_image',
        'author',
        'published_at',
        'status',
        'tags',
        'reading_time',
        'meta_title',
        'meta_description',
        'subtitle',
        'category',
        'series',
        'content_blocks',
        'language',
        'is_featured',
        'canonical_url',
    ];

    protected $casts = [
        'tags' => 'array',
        'published_at' => 'datetime',
        'content_blocks' => 'array',
        'is_featured' => 'boolean',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(PostTranslation::class);
    }
}
