<?php

namespace Modules\Blog\Entities;

use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class PostTranslation extends Model
{
    use AsSource;

    protected $table = 'post_translations';

    protected $fillable = [
        'post_id',
        'lang',
        'slug',
        'title',
        'subtitle',
        'excerpt',
        'content',
        'meta_title',
        'meta_description',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
