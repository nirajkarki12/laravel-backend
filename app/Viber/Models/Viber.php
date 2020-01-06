<?php

namespace App\Viber\Models;

use Illuminate\Database\Eloquent\Model;
use App\User\Models\User;

class Viber extends Model
{
    const MESSAGE       = 'message';
    const SUBSCRIBED    = 'subscribed';
    const UNSUBSCRIBED  = 'unsubscribed';
    const CONVERSATION  = 'conversation_started';
    const DELIVERED     = 'delivered';
    const SEEN          = 'seen';
    const FAILED        = 'failed';

    protected $table='viber_users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id','viber_id','mobile','email','registration_code','subscribed'
    ];


    public function user() {
        return $this->belongsTo(User::Class);
    }
}
