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

        if(array_key_exists('text', $senderMessage) && $senderMessage['text'] === 'code-check' && $senderMessage['tracking_data'] !== 'code-check' && $viberUser->mobile)
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
            $reply = 'You haven\'t\' registered yet for Leader Program. Please register from below link';
            $this->sendMessage($sender['id'], $reply);
            $this->sendMessage($sender['id'], 'https://gundruknetwork.com/the_leader_audition/', null, $keyboard, 'url');
          }
        }elseif($senderMessage['tracking_data'] === 'code-check' && array_key_exists('text', $senderMessage))
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
                      'text' => "The Leader is a 'TV reality Show' where by contestant compete to be the ultimate leader and win a lavish apartment in Kathmandu, brand new car, twenty lakhs and more importantly opportunity in national politics. \r\n To know more about the show visit the link below.",
                      'media' => 'https://theleadernepal.com/');
                  $messageType = 'url';
                  break;

                case 'how-to-register':
                  $reply = array(
                    'text' => "1) Download gundruk app in playstore \nor \nvisit: www.theleadernepal.com \n2) Click the registration button and fill the form. \n3) Pay \n4) You will receive the code once successful. \r\n\nSee the video to know more:",
                    'media' =>'https://youtu.be/mAa9sKwQ3Tk'
                    );
                  $messageType = 'url';
                  break;

                case 'code-check':
                  $reply = 'Please input your mobile number used during registration.';
                  $trackingKey = 'code-check';
                  break;

                case 'notice':
                  $reply = "1) Eligibility for registration \r\n• Nepali residing in any part of the world. \r\n• Fluent in nepali national language. \r\n• Literate \r\n• 18 plus in age. \r\n• No criminal background. \r\n• Not associated with production team. \n2) Registration Fee \r\n• Inside Nepal: Rs 1500 \r\n• Outside Nepal: $15 \n3) Audition Date and venue \r\n• To be announced soon.";
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
                  $reply = "While you wait for the audition date of Leader you can play the Gundruk Quiz, Gundruk Fanfani and earn cash prizes. \r\n\nDownload Gundruk App and start playing. \r\n• Android: http://bit.ly/gundrukapp \r\n• Ios: Coming soon";
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
                  "Text" => "<font color=\"#494E67\"><b>About The Leader </b></font>",
                  "TextSize" => "regular",
                  "ActionType" => "reply",
                  "ActionBody" => "about-the-show",
                  "TextHAlign" => "center",
                  "TextVAlign" => "bottom",
                  "BgColor" => "#fefefe",
                  "Image" => "https://gundruknetwork.com/finance/public/icons/about-us.png"
                ),
                array(
                  "Columns" => 2,
                  "Rows" => 2,
                  "Text" => "<font color=\"#494E67\"><b>How to Register</b></font>",
                  "TextSize" => "regular",
                  "ActionType" => "reply",
                  "ActionBody" => "how-to-register",
                  "TextHAlign" => "center",
                  "TextVAlign" => "bottom",
                  "BgColor" => "#fefefe",
                  "Image" => "https://gundruknetwork.com/finance/public/icons/register.png"
                ),
                array(
                  "Columns" => 2,
                  "Rows" => 2,
                  "Text" => "<font color=\"#494E67\"><b>Registration Status</b></font>",
                  "TextSize" => "regular",
                  "ActionType" => "reply",
                  "ActionBody" => "code-check",
                  "TextHAlign" => "center",
                  "TextVAlign" => "bottom",
                  "BgColor" => "#fefefe",
                  "Image" => "https://gundruknetwork.com/finance/public/icons/reg.png"
                ),
                array(
                  "Columns" => 2,
                  "Rows" => 2,
                  "Text" => "<font color=\"#494E67\"><b>Notice</b></font>",
                  "TextSize" => "regular",
                  "ActionType" => "reply",
                  "ActionBody" => "notice",
                  "TextHAlign" => "center",
                  "TextVAlign" => "bottom",
                  "BgColor" => "#fefefe",
                  "Image" => "https://gundruknetwork.com/finance/public/icons/notice.png"
                ),
                array(
                  "Columns" => 2,
                  "Rows" => 2,
                  "Text" => "<font color=\"#494E67\"><b>Social Media</b></font>",
                  "TextSize" => "regular",
                  "ActionType" => "reply",
                  "ActionBody" => "social-media-links",
                  "TextHAlign" => "center",
                  "TextVAlign" => "bottom",
                  "BgColor" => "#fefefe",
                  "Image" => "https://gundruknetwork.com/finance/public/icons/socialmedia.png"
                ),
                array(
                  "Columns" => 2,
                  "Rows" => 2,
                  "Text" => "<font color=\"#494E67\"><b>More</b></font>",
                  "TextSize" => "regular",
                  "ActionType" => "reply",
                  "ActionBody" => "more",
                  "TextHAlign" => "center",
                  "TextVAlign" => "bottom",
                  "BgColor" => "#fefefe",
                  "Image" => "https://gundruknetwork.com/finance/public/icons/gundruklogo.png"
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
