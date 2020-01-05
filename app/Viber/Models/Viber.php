<?php

namespace App\Viber\Models;

use Illuminate\Database\Eloquent\Model;

class Viber extends Model
{
    const MESSAGE       = 'message';
    const SUBSCRIBED    = 'subscribed';
    const UNSUBSCRIBED  = 'unsubscribed';
    const CONVERSATION  = 'conversation_started';
    const DELIVERED     = 'delivered';
    const SEEN          = 'seen';
    const FAILED        = 'failed';
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
