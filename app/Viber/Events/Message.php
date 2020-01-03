<?php

namespace App\Viber\Events;

use Illuminate\Queue\SerializesModels;
use App\Viber\Events\ViberEvent;

class Message extends ViberEvent
{
    use SerializesModels;

    /**
    * viber user 
    */
    protected $sender;

    /**
    * message sent to pa
    */
    protected $message;

    public function __construct(array $data, $data1)
    {
        parent::__construct($data, $data1);
    }

    public function getSender(){
        return $this->sender;
    }

    public function getMessage(){
        return $this->message;
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
