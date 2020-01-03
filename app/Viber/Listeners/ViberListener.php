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
            $this->onMessageReceived($event)
        }
    }

    public function onMessageReceived(Message $event)
    {
        $message = $event->getMessage();

        $sender = $event->getSender();

        $this->sendMessage($sender, $message);

    }

    public function sendMessage($receiver, $message)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://chatapi.viber.com/pa/send_message",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => "{\n   \"receiver\":\"PIaAAXFD3ORtQqh/KG9XdQ==\",\n   \"min_api_version\":1,\n   \"sender\":{\n      \"name\":\"John McClane\",\n      \"avatar\":\"http://avatar.example.com\"\n   },\n   \"tracking_data\":\"tracking data\",\n   \"type\":\"text\",\n   \"text\":\"Hello world!\"\n}",
          CURLOPT_HTTPHEADER => array(
            "content-type: application/json",
            "x-viber-auth-token: 4ad7c1c218e7d728-e92ed8f87672532e-5bdac0ddf6641518"
          ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
          echo "cURL Error #:" . $err;
        } else {
          echo $response;
        }
    }
}
