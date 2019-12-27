<?php

namespace App\LeaderRegistration\Models;

use Illuminate\Database\Eloquent\Model;

class AdminAudition extends Model {

    protected $connection='mysql2';

    protected $fillable=['admin_id','audition_id'];

}
