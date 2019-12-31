<?php

namespace App\Sms\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\LeaderRegistration\Models\LeaderRegistration;

class SendSms
{
    use SerializesModels;

    public $audition;
    
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(LeaderRegistration $audition)
    {
        $this->audition = $audition;
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
