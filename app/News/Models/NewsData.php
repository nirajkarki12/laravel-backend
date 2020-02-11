<?php

namespace App\News\Models;

use Illuminate\Database\Eloquent\Model;

class NewsData extends Model
{
    protected $guarded = [];

    protected $casts = [
        'files'     => 'array',
        'videos'    => 'array'
    ];
}
