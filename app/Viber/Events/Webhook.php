<?php

namespace App\Viber\Events;
use App\Viber\Events\ViberEvent;

use Illuminate\Queue\SerializesModels;

class Webhook extends ViberEvent
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
