<?php

namespace App\LeaderRegistration\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Mailer;

use App\LeaderRegistration\Models\LeaderRegistration;
use App\Common\Models\Setting;

class SendWelcomeMail implements ShouldQueue
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
    public function handle(Mailer $mailer)
    {
        $siteIcon = Setting::where('key', 'site_icon')->first();
        $email = $this->audition->email;
        $subject = 'Leader Registration';
        $mailer->send('leaderregistration::auditionemail', ['email_data' => $this->audition, 'site_icon' => $siteIcon->value], function ($message) use ($email, $subject) {
            $message->to($email)->subject($subject);
        });
    }
}
