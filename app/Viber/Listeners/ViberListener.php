<?php

namespace App\Viber\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Viber\Events\Conversation;
use App\Viber\Events\Subscribed;
use App\Viber\Events\Message;
use App\Viber\Events\Unsubscribed;
use App\Viber\Models\Viber;

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
        }elseif ($event instanceOf Conversation) {
            return $this->onConversation($event);
        }elseif ($event instanceOf Unsubscribed) {
            return $this->onUnsubscribed($event);
        }
    }

    public function onMessageReceived(Message $event)
    {
        $sender = $event->getSender();
        $message = $this->otherResponse($event->getMessage(), $sender['name']);
        $keyboard = $this->getKeyboard();
        if($message) 
          $this->sendMessage($sender['id'], $message, $keyboard);
    }

    public function onSubscribed(Subscribed $event)
    {
        $user = $event->getUser();

        $message = "Thank you for subscribing.";
        $msg = "Say 'hi' to start conversation.";
        $this->sendMessage($user['id'], $message);
        $this->sendMessage($user['id'], $msg);

        $viberUser = Viber::where('viber_id', $user['id'])->first();
        
        if(!$viberUser)
        {
          Viber::create([
              'viber_id' => $user['id'],
              'subscribed' => true
          ]);
        }
    }

    public function onUnsubscribed(Unsubscribed $event)
    {
        $userId = $event->getUserId();

        $message = "Goodbye user, Hope to see you soon!!";
        $this->sendMessage($userId, $message);

        if($viberUser = Viber::where('viber_id', $userId)->first())
        {
          $viberUser->subscribed = false;
          $viberUser->update();
        }
    }

    public function onConversation(Conversation $event)
    {
        $user = $event->getUser();
        // $message = "Hi " .$user['name'] ."!";
        $msg = "Say 'hi' to start conversation.";
        // $this->sendMessage($user['id'], $message);
        $this->sendMessage($user['id'], $msg);
    }

    public function otherResponse($message, $sender)
    {
      $greetings = array('hello', 'hi', 'hey', 'what\'s up', 'whats up');
      $keyboard = array('about', 'register', 'registration', 'code-check', 'registration-location', 'social-media-links');

      if(array_key_exists('text', $message))
      {
        $message = strtolower($message['text']);
        switch (true) {
            case (in_array($message, $greetings)):{
              $reply = ucfirst($message).' '. ($sender ? $sender.'! ': ''). 'How can i help you?';
              break;
            }
            
            default:{
              switch ($message) {
                case 'code-check':
                  $reply = 'Please input your mobile number used during registration.';
                  break;
                
                default:
                  $reply = 'Sorry, I don\'t understand. Please select any option from keyboard.';
                  break;
              }
            }
        }
        return $reply;
      }

    }

    public function getKeyboard()
    {
      return [
            "Type" => "keyboard",
            "DefaultHeight" => true,
            "Buttons" => array(
                array(
                  "Columns" => 2,
                  "Rows" => 2,
                  "Text" => "<font color=\"#494E67\"><b>About the show</b></font>",
                  "TextSize" => "regular",
                  "ActionType" => "reply",
                  "ActionBody" => "about",
                  "BgColor" => "#f7bb3f",
                  "Image" => "https://s18.postimg.org/9tncn0r85/sushi.png"
                ),
                array(
                  "Columns" => 2,
                  "Rows" => 2,
                  "Text" => "<font color=\"#494E67\"><b>How To Register</b></font>",
                  "TextSize" => "regular",
                  "ActionType" => "reply",
                  "ActionBody" => "register",
                  "BgColor" => "#f7bb3f",
                  "Image" => "https://s18.postimg.org/9tncn0r85/sushi.png"
                ),
                array(
                  "Columns" => 2,
                  "Rows" => 2,
                  "Text" => "<font color=\"#494E67\"><b>Registration</b></font>",
                  "TextSize" => "regular",
                  "ActionType" => "reply",
                  "ActionBody" => "registration",
                  "BgColor" => "#f7bb3f",
                  "Image" => "https://s18.postimg.org/9tncn0r85/sushi.png"
                ),
                array(
                  "Columns" => 2,
                  "Rows" => 2,
                  "Text" => "<font color=\"#494E67\"><b>Registration Code Check</b></font>",
                  "TextSize" => "regular",
                  "ActionType" => "reply",
                  "ActionBody" => "code-check",
                  "BgColor" => "#f7bb3f",
                  "Image" => "https://s18.postimg.org/9tncn0r85/sushi.png"
                ),
                array(
                  "Columns" => 2,
                  "Rows" => 2,
                  "Text" => "<font color=\"#494E67\"><b>Registration Locations</b></font>",
                  "TextSize" => "regular",
                  "ActionType" => "reply",
                  "ActionBody" => "registration-location",
                  "BgColor" => "#f7bb3f",
                  "Image" => "https://s18.postimg.org/9tncn0r85/sushi.png"
                ),
                array(
                  "Columns" => 2,
                  "Rows" => 2,
                  "Text" => "<font color=\"#494E67\"><b>Social Media Links</b></font>",
                  "TextSize" => "regular",
                  "ActionType" => "reply",
                  "ActionBody" => "social-media-links",
                  "BgColor" => "#f7bb3f",
                  "Image" => "https://s18.postimg.org/9tncn0r85/sushi.png"
                ),
            )
        ];
    }

    public function sendMessage($receiver, $message, $keyboard = null)
    {
        $curl = curl_init();

        $data = [
            "receiver" => $receiver,
            "min_api_version" => 1,
            "sender" => [
                "name" => "Bharyang Venture",
                "avatar" => "https://media-direct.cdn.viber.com/pg_download?pgtp=icons&dlid=0-04-01-d6bdce8a229f79822e6761ba932a84a37aa4594a803d49c2d88bdc0e1de5997b&fltp=jpg&imsz=0000"
            ],
            "tracking_data" => rand(),
            "type" => "text",
            "text" => $message
        ];

        if($keyboard) {
          $data['keyboard'] = $keyboard;
        }

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

        if($err){
          return $err;
        }else{
          return $response;
        }
    }
}
