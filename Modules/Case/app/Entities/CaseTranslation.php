<?php

namespace Modules\Case\Entities;

use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class CaseTranslation extends Model
{
    use AsSource;

    protected $table = 'case_translations';

    protected $fillable = [
        'case_id',
        'lang',
        'slug',
        'property_title',
        'property_price',
        'location',
        'livingArea',
        'tag',
        'status',
        'type',
    ];

    public function case()
    {
        return $this->belongsTo(CaseModel::class, 'case_id');
    }
}
