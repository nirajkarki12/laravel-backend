<?php

namespace App\Common\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        'App\Sms\Events\SendSms' => [
            'App\Sms\Listeners\SendSmsListener',   
        ],
        'App\Viber\Events\Webhook' => [
            'App\Viber\Listeners\ViberListener',
        ],
        'App\Viber\Events\Conversation' => [
            'App\Viber\Listeners\ViberListener',
        ],
        'App\Viber\Events\Delivered' => [
            'App\Viber\Listeners\ViberListener',
        ],
        'App\Viber\Events\Failed' => [
            'App\Viber\Listeners\ViberListener',
        ],
        'App\Viber\Events\Seen' => [
            'App\Viber\Listeners\ViberListener',
        ],
        'App\Viber\Events\Subscribed' => [
            'App\Viber\Listeners\ViberListener',
        ],
        'App\Viber\Events\Unsubscribed' => [
            'App\Viber\Listeners\ViberListener',
        ],
        'App\Viber\Events\Message' => [
            'App\Viber\Listeners\ViberListener',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}

