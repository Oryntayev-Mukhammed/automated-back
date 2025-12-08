<?php

namespace Modules\Blog\Entities;

use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class Post extends Model
{
    use AsSource;

    protected $table = 'posts';

    protected $fillable = [
        'slug',
        'title',
        'excerpt',
        'coverImage',
        'author',
        'published_at',
    ];
}
