<?php

namespace App\Viber\Models;

use Illuminate\Database\Eloquent\Model;

class Viber extends Model
{
    const WEBHOOK       = 'webhook';
    const MESSAGE       = 'message';
    const DELIVERED     = 'delivered';
    const SEEN          = 'seen';
    const FAILED        = 'failed';
    const SUBSCRIBED    = 'subscribed';
    const UNSUBSCRIBED  = 'unsubscribed';
    const CONVERSATION  = 'conversation_started';

    public static $webhookEvents = array(
        self::DELIVERED     => 'delivered',
        self::SEEN          => 'seen',
        self::FAILED        => 'failed',
        self::SUBSCRIBED    => 'subscribed',
        self::UNSUBSCRIBED  => 'unsubscribed',
        self::CONVERSATION  => 'conversation_started',
    );

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
