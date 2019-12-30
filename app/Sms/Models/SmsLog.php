<?php

namespace App\Sms\Models;

use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    protected $connection='mysql2';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type', 'user_id','message','response_code','status','request','response'
    ];

    protected $casts = [
        'request' => 'array',
        'response' => 'array'
    ];

}
