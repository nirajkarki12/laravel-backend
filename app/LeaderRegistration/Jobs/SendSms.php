<?php

namespace App\LeaderRegistration\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Sms\Events\SendSms as SendSmsEvent;
use App\LeaderRegistration\Models\LeaderRegistration;

class SendSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $audition;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(LeaderRegistration $audition)
    {
        $this->audition = $audition;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        event(new SendSmsEvent($this->audition));
    }
}
