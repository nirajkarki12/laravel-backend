<?php

namespace App\Viber\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Viber\Events\Conversation;
use App\Viber\Events\Subscribed;
use App\Viber\Events\Message;
use App\Viber\Events\Unsubscribed;

use App\Viber\Models\Viber;
use App\LeaderRegistration\Models\LeaderRegistration;

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
        }elseif ($event instanceOf Conversation)
        {
            return $this->onConversation($event);
        }elseif ($event instanceOf Unsubscribed)
        {
            return $this->onUnsubscribed($event);
        }
    }

    public function onMessageReceived(Message $event)
    {
        $sender = $event->getSender();
        $keyboard = $this->getKeyboard();
        $senderMessage = $event->getMessage();

        $reply = '';
        $trackingData = '';

        $viberUser = Viber::where('viber_id', $sender['id'])->first();

        if(array_key_exists('text', $senderMessage) && $senderMessage['text'] === 'code-check' && $viberUser->mobile)
        {
          if($auditionRegistration = LeaderRegistration::where('number', $viberUser->mobile)->first())
          {
            if($auditionRegistration->registration_code)
            {
              $reply = "Your registration code is - '" .$auditionRegistration->registration_code ."'";
              $this->sendMessage($sender['id'], $reply, null, $keyboard);
            }
          }else{
            $reply = 'You haven\'t\' registered yet for Leader Program, Please register from here';
            $this->sendMessage($sender['id'], $reply, null, $keyboard);
            $this->sendMessage($sender['id'], 'https://gundruknetwork.com/the_leader_audition/', null, $keyboard, 'url');
          }
        }elseif($senderMessage['tracking_data'] === 'code-check' && array_key_exists('text', $senderMessage) && $senderMessage['text'] !== 'code-check')
        {
          $auditionRegistration = LeaderRegistration::where('number', $senderMessage['text'])->first();
          // updating viber users table
          if($auditionRegistration = LeaderRegistration::where('number', $senderMessage['text'])->first())
          {
            if($viberUser)
            {
              $viberUser->mobile = $senderMessage['text'];
              $viberUser->user_id = $auditionRegistration->user_id;
              $viberUser->update();
            }
            if(!$auditionRegistration->payment_status)
            {
              $reply = "Your registration payment isn't received yet, Please complete your payment using eSewa/Khalti or contact us at 01-0692904.";
              $this->sendMessage($sender['id'], $reply, null, $keyboard);

            }elseif($auditionRegistration->registration_code)
            {
              $reply = "Your registration code is - '" .$auditionRegistration->registration_code ."'";
              $this->sendMessage($sender['id'], $reply, null, $keyboard);

            }
          }else{
            $reply = 'You haven\'t\' registered yet for Leader Program, Please register from here';
            $this->sendMessage($sender['id'], $reply, null, $keyboard);
            $this->sendMessage($sender['id'], 'https://gundruknetwork.com/the_leader_audition/', null, $keyboard, 'url');
          }

        }else{
          $botRes = $this->botResponse($senderMessage, $sender['name']);
          $reply = $botRes['msg'];
          $trackingData = $botRes['trackingKey'];
          $this->sendMessage($sender['id'], $reply, $trackingData, $keyboard);
        }

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

    public function botResponse($message, $sender)
    {
      $greetings = array('hello', 'hi', 'hey', 'what\'s up', 'whats up');
      $keyboard = array('about', 'register', 'registration', 'code-check', 'registration-location', 'social-media-links');
      $trackingKey = null;

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
                  $trackingKey = 'code-check';
                  break;
                
                default:
                  $reply = 'Sorry, I don\'t understand. Please select any option from keyboard.';
                  break;
              }
            }
        }
        return ['msg' => $reply, 'trackingKey' => $trackingKey];
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

    public function sendMessage($receiver, $message, $trackingData = null, $keyboard = null, $messageType = null)
    {
        $curl = curl_init();

        $data = [
            "receiver" => $receiver,
            "min_api_version" => 1,
            "sender" => [
                "name" => "Bharyang Venture",
                "avatar" => "https://media-direct.cdn.viber.com/pg_download?pgtp=icons&dlid=0-04-01-d6bdce8a229f79822e6761ba932a84a37aa4594a803d49c2d88bdc0e1de5997b&fltp=jpg&imsz=0000"
            ],
            "tracking_data" => ($trackingData) ? $trackingData : 'leader',
        ];

        if($keyboard) {
          $data['keyboard'] = $keyboard;
        }

        switch ($messageType) {
          case 'location':
            $data['type'] = $messageType;
            $data['location'] = $message;
            break;

          case 'url':
            $data['type'] = $messageType;
            $data['media'] = $message;
            break;

          case 'contact':
            $data['type'] = $messageType;
            $data['contact'] = $message;
            break;

          case 'video':
            $data['type'] = $messageType;
            $data['media'] = $message;
            break;
          
          default:
            $data['type'] = 'text';
            $data['text'] = $message;
            break;
        }

        //location
          // "type" => "location",
          // "location" => [
          //   "lat" => "37.7898",
          //   "lon" => "-122.3942"
          // ]
        // URL
          // "type" => "url",
          // "media" => "http://www.website.com/go_here"
        // Contact
          // "type" => "contact",
          // "contact" => [
          //   "name" => "Itamar",
          //   "phone_number" => "+972511123123"
          // ]
        // video
          // "type" => "video",
          // "media" => "http://www.images.com/video.mp4",
          // "thumbnail" => "http://www.images.com/thumb.jpg",
          // "size" => 10000,
          // "duration" => 10
          

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
            "X-Viber-Auth-Token: 4adea48e1427def6-a74c80eb1a7316d9-fd452a72f151651b"
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
