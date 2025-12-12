<?php

namespace Modules\Contact\Entities;

use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class ContactSubmission extends Model
{
    use AsSource;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'specialist',
        'date',
        'time',
        'message',
        'locale',
        'status',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];
}
