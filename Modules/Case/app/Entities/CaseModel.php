<?php

namespace Modules\Case\Entities;

use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class CaseModel extends Model
{
    use AsSource;

    protected $table = 'cases';

    protected $fillable = [
        'property_title',
        'property_img',
        'property_price',
        'category',
        'location',
        'livingArea',
        'tag',
        'status',
        'type',
        'slug',
    ];
}
