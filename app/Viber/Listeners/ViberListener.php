<?php

namespace App\Viber\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Viber\Events\Webhook;
use App\Viber\Events\Conversation;
use App\Viber\Events\Delivered;
use App\Viber\Events\Failed;
use App\Viber\Events\Seen;
use App\Viber\Events\Subscribed;
use App\Viber\Events\Unsubscribed;
use App\Viber\Events\Message;

class ViberListener
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {

        if($event instanceOf Message)
        {
            return $this->onMessageReceived($event);
        }
    }

    public function onMessageReceived(Message $event)
    {

        $message = $event->getMessage();

        $sender = $event->getSender();
        $this->sendMessage($sender['id'], $message);

    }

    public function sendMessage($receiver, $message)
    {
        $curl = curl_init();

        $data = [
            "receiver" => $receiver,
            "min_api_version" => 1,
            "sender" => [
                "name" => "Bharyang Venture",
                "avatar" => "http://avatar.example.com"
            ],
            "tracking_data" =>"tracking data",
            "type" => "text",
            "text" => $message['text']
        ];

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://chatapi.viber.com/pa/send_message",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => json_encode($data),
          CURLOPT_HTTPHEADER => array(
            "content-type: application/json",
            "x-viber-auth-token: 4ad7c1c218e7d728-e92ed8f87672532e-5bdac0ddf6641518"
          ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);
    }
}
