<?php

namespace App\Finance\Models;

use Illuminate\Database\Eloquent\Model;

class Network extends Model
{
    protected $connection='mysql';

    protected $fillable=['name'];
}
