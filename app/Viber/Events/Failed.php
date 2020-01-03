<?php

namespace App\Viber\Events;

use Illuminate\Queue\SerializesModels;
use App\Viber\Events\ViberEvent;

class Failed extends ViberEvent
{
    use SerializesModels;

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
