<?php

namespace App\Viber\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Viber\Events\Conversation;
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
            $this->onMessageReceived($event);
        }elseif($event instanceOf Subscribed)
        {
            $this->onSubscribed($event);
        }elseif ($event instanceOf Unsubscribed) {
          $this->onUnsubscribed($event);
        }
    }

    public function onMessageReceived(Message $event)
    {
        $message = $event->getMessage();

        $sender = $event->getSender();
        $this->sendMessage($sender['id'], $message['text']);
    }

    public function onSubscribed(Subscribed $event)
    {
        $user = $event->getUser();

        $message = "Hi " .$user['name'] ."!";
        $msg = "How can i help you?";
        $this->sendMessage($user['id'], $message);
        $this->sendMessage($user['id'], $msg);
    }

    public function onSubscribed(Unsubscribed $event)
    {
        $user = $event->getUser();

        $message = "Goodbye " .$user['name'] ."! Hope to see you soon.";
        $this->sendMessage($user['id'], $message);
    }

    public function sendMessage($receiver, $message)
    {
        $curl = curl_init();

        $data = [
            "receiver" => $receiver,
            "min_api_version" => 1,
            "sender" => [
                "name" => "Bharyang Venture",
                "avatar" => "https://media-direct.cdn.viber.com/pg_download?pgtp=icons&dlid=0-04-01-d6bdce8a229f79822e6761ba932a84a37aa4594a803d49c2d88bdc0e1de5997b&fltp=jpg&imsz=0000"
            ],
            "tracking_data" =>"tracking data",
            "type" => "text",
            "text" => $message
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
            "X-Viber-Auth-Token: 4ad7c1c218e7d728-e92ed8f87672532e-5bdac0ddf6641518"
          ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);
    }
}
