<?php

namespace Modules\Case\Entities;

use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Orchid\Attachment\Attachable;

class CaseModel extends Model
{
    use AsSource, Attachable;

    protected $table = 'cases';

    protected $fillable = [
        'title',
        'cover_image',
        'domain',
        'summary',
        'property_title',
        'property_price',
        'category',
        'location',
        'livingArea',
        'tag',
        'status',
        'type',
        'slug',
        'language',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(CaseTranslation::class, 'case_id');
    }
}
