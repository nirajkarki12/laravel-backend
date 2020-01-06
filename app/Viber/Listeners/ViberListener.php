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
            $reply = 'You haven\'t\' registered yet for Leader Program, Please register from below link';
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
            $reply = 'You haven\'t\' registered yet for Leader Program, Please register from below link';
            $this->sendMessage($sender['id'], $reply, null, $keyboard);
            $this->sendMessage($sender['id'], 'https://gundruknetwork.com/the_leader_audition/', null, $keyboard, 'url');
          }

        }else{
          $botRes = $this->botResponse($senderMessage, $sender['name']);
          $reply = $botRes['msg'];
          $trackingData = $botRes['trackingKey'];
          $type = $botRes['type'];
          switch ($type) {
            case 'about-the-show':
              $this->sendMessage($sender['id'], $reply['text']);
              $this->sendMessage($sender['id'], $reply['media'], $trackingData, $keyboard, 'url');
              break;

            case 'how-to-register':
              $this->sendMessage($sender['id'], $reply['text']);
              $this->sendMessage($sender['id'], $reply['media'], $trackingData, $keyboard, 'video');
              break;

            case 'social-media-links':
              $this->sendMessage($sender['id'], $reply['text']);
              foreach ($reply['urls'] as $url) {
                $this->sendMessage($sender['id'], $url, $trackingData, $keyboard, 'url');
              }
              break;
            
            default:
              $this->sendMessage($sender['id'], $reply, $trackingData, $keyboard);
              break;
          }
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
        }else{
          $viberUser->subscribed = true;
          $viberUser->update();
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
        $msg = "Say 'hi' to start conversation.";
        $this->sendMessage($user['id'], $msg);
    }

    public function botResponse($message, $sender)
    {
      $greetings = array('hello', 'hi', 'hey', 'what\'s up', 'whats up');
      $keyboard = array('about-the-show', 'how-to-register', 'code-check', 'notice', 'social-media-links', 'more');
      $trackingKey = null;
      $messageType = 'text';

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
                case 'about-the-show':
                  $reply = array(
                      'text' => 'The Leader is a reality TV show that features the members of the public. It aims to give a
                      particular country a leader that has the vision, zeal and potential to change the country. The
                      leader who can drive the nation and take it forward. The original concept of the program is to
                      find energetic and dynamic leader who will lead the country in the near future. The best part
                      about this show is that as an individual, you will realize that it is possible to change your
                      country.',
                      'media' => 'https://theleadernepal.com/');
                  $messageType = 'url';
                  break;

                case 'how-to-register':
                  $reply = array(
                    'text' => '',
                    'media' => 'https://www.facebook.com/theleadernepal/videos/2521932888018918/',
                    'thumbnail' => '',
                    'size' => '',
                    'duration' =>''
                    );
                  $messageType = 'video';
                  break;

                case 'code-check':
                  $reply = 'Please input your mobile number used during registration.';
                  $trackingKey = 'code-check';
                  break;

                case 'notice':
                  $reply = 'Any Nepali residing in any part of the world can be a part of this show.
                            A small condition is that they should abide by following checklist:
                            1 Fluent in Nepali national language
                            2 Literate
                            3 Age above 18
                            4 Employees of the broadcasting channel
                            5 Host’s family and close aide
                            6 Family and close aide of production team
                            7 Judge family and close aide
                            8 Criminal background';
                  break;

                case 'social-media-links':
                  $reply = array(
                    'text' => 'Social Media Links',
                    'urls' => array(
                      'https://theleadernepal.com/',
                      'https://www.facebook.com/theleadernepal/'
                    )
                    );
                  $messageType = 'url';
                  break;

                case 'more':
                  $reply = 'Gundruk Quiz is a mobile trivia game where players can play for free and win prize money. The best part of it is every user can win money depending on the levels they cross. Higher the level, greater the amount earned. Every level has fifteen questions. Upon the completion of one level, you reach the next level. As the level increases, the amount to be won is increased. There are lifelines which help you while playing.';
                  break;
                
                default:
                  $reply = 'Sorry, I don\'t understand. Please select any option from keyboard.';
                  break;
              }
            }
        }
        return ['msg' => $reply, 'trackingKey' => $trackingKey, 'type' => $messageType];
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
                  "ActionBody" => "about-the-show",
                  "BgColor" => "#f7bb3f",
                  "Image" => "https://s18.postimg.org/9tncn0r85/sushi.png"
                ),
                array(
                  "Columns" => 2,
                  "Rows" => 2,
                  "Text" => "<font color=\"#494E67\"><b>How to register</b></font>",
                  "TextSize" => "regular",
                  "ActionType" => "reply",
                  "ActionBody" => "how-to-register",
                  "BgColor" => "#f7bb3f",
                  "Image" => "https://s18.postimg.org/9tncn0r85/sushi.png"
                ),
                array(
                  "Columns" => 2,
                  "Rows" => 2,
                  "Text" => "<font color=\"#494E67\"><b>Get your registration code</b></font>",
                  "TextSize" => "regular",
                  "ActionType" => "reply",
                  "ActionBody" => "code-check",
                  "BgColor" => "#f7bb3f",
                  "Image" => "https://s18.postimg.org/9tncn0r85/sushi.png"
                ),
                array(
                  "Columns" => 2,
                  "Rows" => 2,
                  "Text" => "<font color=\"#494E67\"><b>Notice</b></font>",
                  "TextSize" => "regular",
                  "ActionType" => "reply",
                  "ActionBody" => "notice",
                  "BgColor" => "#f7bb3f",
                  "Image" => "https://s18.postimg.org/9tncn0r85/sushi.png"
                ),
                array(
                  "Columns" => 2,
                  "Rows" => 2,
                  "Text" => "<font color=\"#494E67\"><b>Social media links</b></font>",
                  "TextSize" => "regular",
                  "ActionType" => "reply",
                  "ActionBody" => "social-media-links",
                  "BgColor" => "#f7bb3f",
                  "Image" => "https://s18.postimg.org/9tncn0r85/sushi.png"
                ),
                array(
                  "Columns" => 2,
                  "Rows" => 2,
                  "Text" => "<font color=\"#494E67\"><b>More</b></font>",
                  "TextSize" => "regular",
                  "ActionType" => "reply",
                  "ActionBody" => "more",
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
