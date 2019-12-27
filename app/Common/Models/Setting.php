<?php

namespace App\Common\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model {

    protected $connection='mysql2';

    protected $fillable=['key','value'];
}
