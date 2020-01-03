<?php

namespace App\Viber\Events;

use Illuminate\Queue\SerializesModels;
use App\Viber\Events\ViberEvent;

class Failed extends ViberEvent
{
    use SerializesModels;

    /**
    * viber user id
    */
    protected $user_id;

    /**
    * A string describing the failure
    */
    protected $desc;

    public function getUserId(){
        return $this->user_id;
    }

    public function getDesc(){
        return $this->desc;
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
