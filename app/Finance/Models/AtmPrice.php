<?php

namespace App\Finance\Models;

use Illuminate\Database\Eloquent\Model;

class AtmPrice extends Model
{
    protected $connection='mysql';
    protected $fillable=['bank_from','bank_to','charge','network', 'note'];
    protected $casts = [
    	'bank_to' => 'array',
    	'network' => 'array'
    ];
}
