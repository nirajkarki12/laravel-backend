<?php

namespace App\Viber\Events;

use Illuminate\Queue\SerializesModels;
use App\Viber\Events\ViberEvent;

class Seen extends ViberEvent
{
    use SerializesModels;

    /**
    * viber user id
    */
    protected $user_id;

    public function getUserId(){
        return $this->user_id;
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
