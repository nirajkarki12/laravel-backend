<?php

namespace App\Viber\Events;

use Illuminate\Queue\SerializesModels;
use App\Viber\Events\ViberEvent;

class Conversation extends ViberEvent
{
    use SerializesModels;

    /**
    * viber user
    */
    protected $user;

    /**
    * the specific type of conversation_started event / open only for now
    */
    protected $type;

    /**
    * Any additional parameters added to the deep link used to access the conversation passed as a string
    */
    protected $context;

    /**
    * indicated whether a user is already subscribed
    * true if subscribed and false otherwise
    */
    protected $subscribed;


    public function getUser(){
        return $this->user;
    }

    public function getType(){
        return $this->type;
    }

    public function  getContext(){
        return $this->context;
    }

    public function  getSubscribed(){
        return $this->subscribed;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}
