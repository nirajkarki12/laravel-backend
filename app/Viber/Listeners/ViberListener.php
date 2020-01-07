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

        $keyboardTypes = array('about-the-show', 'how-to-register', 'code-check', 'notice', 'social-media-links', 'more');

        $viberUser = Viber::where('viber_id', $sender['id'])->first();

        if(array_key_exists('text', $senderMessage) && $senderMessage['text'] === 'code-check')
        {
          $auditionRegistration = null;

          if(isset($viberUser) && $viberUser->mobile)
          {
            $auditionRegistration = LeaderRegistration::where('number', $viberUser->mobile)->first();
          }
          if(isset($auditionRegistration) && $auditionRegistration->registration_code)
          {
            $reply = "Your registration code is - '" .$auditionRegistration->registration_code ."'";
            $this->sendMessage($sender['id'], $reply, null, $keyboard);
          }else{
            $reply = 'You haven\'t\' registered yet for Leader Program, Please register from below link';
            $this->sendMessage($sender['id'], $reply);
            $this->sendMessage($sender['id'], 'https://gundruknetwork.com/the_leader_audition/', null, $keyboard, 'url');
          }
        }elseif($senderMessage['tracking_data'] === 'code-check' && array_key_exists('text', $senderMessage) && !in_array(strtolower($senderMessage['text']), $keyboardTypes))
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
            $this->sendMessage($sender['id'], $reply);
            $this->sendMessage($sender['id'], 'https://gundruknetwork.com/the_leader_audition/', null, $keyboard, 'url');
          }

        }else{
          $botRes = $this->botResponse($senderMessage, $sender['name']);
          $reply = $botRes['msg'];
          $trackingData = $botRes['trackingKey'];
          $type = $botRes['type'];

          switch ($type) {
            case 'url':
              $this->sendMessage($sender['id'], $reply['text']);
              $this->sendMessage($sender['id'], $reply['media'], $trackingData, $keyboard, 'url');
              break;

            case 'urls':
              $this->sendMessage($sender['id'], $reply['text']);
              foreach ($reply['urls'] as $url) {
                $this->sendMessage($sender['id'], $url, $trackingData, $keyboard, 'url');
              }
              break;

            case 'texts':
              foreach ($reply as $key => $text) {
                $this->sendMessage($sender['id'], $text, $trackingData, $keyboard);
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
                      'text' => "The Leader \r\n It is a reality TV show that features the members of the public. It aims to give a particular country a leader that has the vision, zeal and potential to change the country. The leader who can drive the nation and take it forward.\r\n The original concept of the program is to find energetic and dynamic leader who will lead the country in the near future. The best part about this show is that as an individual, you will realize that it is possible to change your country.\r\n To know more please visit the link below.",
                      'media' => 'https://theleadernepal.com/');
                  $messageType = 'url';
                  break;

                case 'how-to-register':
                  $reply = array(
                    'text' => 'Follow these steps explained on video',
                    'media' =>'https://youtu.be/mAa9sKwQ3Tk'
                    );
                  $messageType = 'url';
                  break;

                case 'code-check':
                  $reply = 'Please input your mobile number used during registration.';
                  $trackingKey = 'code-check';
                  break;

                case 'notice':
                  $reply = "Leader Registration Eligibles & Ineligibles \r\n Any Nepali residing in any part of the world can be a part of this show. A small condition is that they should abide by following checklist: \r\n 1.Should be Fluent in Nepali national language. \r\n 2.Should be Literate. \r\n 3.Should be Age above 18. \r\n 4.Should not be Employees of the broadcasting channel. \r\n 5.Should not be Host’s family and close aide. \r\n 6.Should not be Family and close aide of production team. \r\n 7.Should not be Judge family and close aide. \r\n 8.Should not be Criminal background. \r\nLeader Registration Charge from Nepal - NPR 1,000 and for abroad Candidate - $15.";
                  break;

                case 'social-media-links':
                  $reply = array(
                    'text' => 'Social Media Links',
                    'urls' => array(
                      'https://www.facebook.com/theleadernepal/',
                      'https://www.instagram.com/theleadernepal/',
                      'https://twitter.com/theleadernepal',
                      'https://www.youtube.com/channel/UC7-dwRaU_ZTFqdXGGbUA6lw',
                    )
                  );
                  $messageType = 'urls';
                  break;

                case 'more':
                  $reply = array(
                    'text' => "Gundruk Quiz App \r\n It is a mobile trivia game where players can play for free and win prize money. The best part of it is every user can win money depending on the levels they cross. Higher the level, greater the amount earned. Every level has fifteen questions. \r\n Upon the completion of one level, you reach the next level. As the level increases, the amount to be won is increased. There are lifelines which help you while playing. \r\n Follow these links below to download on Android/IOS.",
                    'urls' => array(
                      'https://play.google.com/store/apps/details?id=com.thesunbi.kbcnepal',
                      'https://apps.apple.com/np/app/kbc-nepal/id1347588056'
                    )
                  );
                  $messageType = 'urls';
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
                  "TextHAlign" => "center",
                  "TextVAlign" => "bottom",
                  "BgColor" => "#f7bb3f",
                  "Image" => "https://finance.grundruknetwork.com/icons/icons-tv.png"
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
                "name" => "The Leader",
                "avatar" => "https://theleadernepal.com/wp-content/uploads/2019/12/theleader_500.png"
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
            "X-Viber-Auth-Token: " .config('services.viber.token')
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
