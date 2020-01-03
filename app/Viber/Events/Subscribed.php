<?php

namespace App\Viber\Events;

use Illuminate\Queue\SerializesModels;
use App\Viber\Events\ViberEvent;

class Subscribed extends ViberEvent
{
    use SerializesModels;

    /**
    * viber subscriber user detail
    */
    protected $user;

    public function getUser(){
        return $this->user;
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
