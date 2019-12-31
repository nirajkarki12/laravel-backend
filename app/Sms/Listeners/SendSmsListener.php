<?php

namespace App\Sms\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Nexmo\Laravel\Facade\Nexmo;
use App\Sms\Events\SendSms;
use App\Sms\Models\SmsLog;

class SendSmsListener
{
    /** 
     * Handle the event.    
     *  
     * @param  SendSms  $event  
     * @return void 
     */ 
    public function handle(SendSms $event)  
    {   
        if(!$event->audition['registration_code'])  
        {   
            if($this->onLeaderRegistration($event)) {
                $event->audition->registration_code_send_count++;
                $event->audition->sms_queue = 0;
                $event->audition->update();
            } 
        }   
        elseif($event->audition['registration_code'])   
        {   
            if($this->onLeaderPayment($event)) {
                $event->audition->registration_code_send_count++;
                $event->audition->sms_queue = 0;
                $event->audition->update();
            }
        }   
    }

    /** 
    * Handle leader registration events.   
    */ 
    public function onLeaderRegistration(SendSms $event) {  
        $audition = $event->audition;

        if($audition['country_code'] === '977') {   
            $mobile = $audition['number'];  
                
        }elseif($audition['country_code']) {    
            $mobile = $audition['country_code'] .$audition['number'];   
        }

        $msg = "Hello ".$audition['name'].". You have registerd for The Leader Successfully. Please proceed for payment to complete registration. For more details link http://bit.ly/leadernp Thank you.";

        if($audition['country_code'] === '977') {   
            return $this->sparrowSms($mobile, $msg, $audition);    
        }else{  
            return $this->nexmoSms($mobile, $msg, $audition);  
        }   
    }

    /** 
    * Handle user payment events.  
    */ 
    public function onLeaderPayment(SendSms $event) {   
        $audition = $event->audition;

        if($audition['country_code'] === '977') {   
            $mobile = $audition['number'];  
        }elseif($audition['country_code']){ 
            $mobile = $audition['country_code'] .$audition['number'];   
        }

        $msg = "Congratulations ".$audition['name']."!. Your registration is successful. Your registration code is '".$audition['registration_code']."'. Please keep this code safe. Visit http://bit.ly/leadernp for more details. Thank you.";

        if($audition['country_code'] === '977') {   
            return $this->sparrowSms($mobile, $msg, $audition);    
        }else{  
            return $this->nexmoSms($mobile, $msg, $audition);  
        }   
    }

    public function nexmoSms($to, $msg, $audition) {
        $data = array(
            'to'   => $to,
            'from' => config('services.nexmo.sms_from'),
            'text' => $msg
        );
        $res = Nexmo::message()->send($data);
        $status = $res->getStatus();
        $responseData = $res->getResponseData();

        $message = $this->getCodeMessages($audition['country_code'], $response_code);

        SmsLog::create([
                    'type' => 'Nexmo',
                    'user_id' => $audition['user_id'],
                    'message' => $message,
                    'response_code' => $status,
                    'status' => ($status === '0') ? true : false,
                    'request' => $data,
                    'response' => $responseData
                ]);

        return ($status === '0') ? true : false;
    }

    public function sparrowSms($to, $msg, $audition) {
        $token = "Lsntwh8k5hh0XFrBgOd5";

        $link = "http://api.sparrowsms.com/v2/sms/";

        $data = [
            'token' => $token,
            'from' => 'InfoSMS',
            'to' => $to,
            'text'=> $msg
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $link);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        $response = json_decode(curl_exec($curl));
        $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if(property_exists($response, 'response_code')){
            $response_code = $response->response_code;
        }else{
            $response_code = '';
        }

        $message = $this->getCodeMessages($audition['country_code'], $response_code);

        SmsLog::create([
                    'type' => 'SparrowSms',
                    'user_id' => $audition['user_id'],
                    'message' => $message,
                    'response_code' => $response_code,
                    'status' => ($response_code === 200) ? true : false,
                    'request' => $data,
                    'response' => $response
                ]);

        return ($response_code === 200) ? true : false;

    }

    public function getCodeMessages($country_code, $code){
        $msg = '';

        if(!$country_code === '977'){
            switch ($code) {
                case '0':
                    $msg = 'Message was delivered successfully';
                    break;

                case '1':
                    $msg = 'Message was not delivered, and no reason could be determined';
                    break;

                case '2':
                    $msg = 'Message was not delivered because handset was temporarily unavailable - retry';
                    break;

                case '3':
                    $msg = 'The number is no longer active and should be removed from your database';
                    break;

                case '4':
                    $msg = 'This is a permanent error: the number should be removed from your database and the user must contact their network operator to remove the bar';
                    break;

                case '5':
                    $msg = 'There is an issue relating to portability of the number and you should contact the network operator to resolve it';
                    break;

                case '6':
                    $msg = 'The message has been blocked by a carrier\'s anti-spam filter';
                    break;

                case '7':
                    $msg = 'The handset was not available at the time the message was sent - retry';
                    break;

                case '8':
                    $msg = 'The message failed due to a network error - retry';
                    break;

                case '9':
                    $msg = 'The user has specifically requested not to receive messages from a specific service';
                    break;

                case '10':
                    $msg = 'There is an error in a message parameter, e.g. wrong encoding flag';
                    break;

                case '11':
                    $msg = 'Nexmo cannot find a suitable route to deliver the message';
                    break;

                case '12':
                    $msg = 'A route to the number cannot be found - confirm the recipient\'s number';
                    break;


                case '13':
                    $msg = 'The target cannot receive your message due to their age';
                    break;

                case '14':
                    $msg = 'The recipient should ask their carrier to enable SMS on their plan';
                    break;

                case '15':
                    $msg = 'The recipient is on a prepaid plan and does not have enough credit to receive your message';
                    break;

                default:
                    $msg = 'Unknown Error';
                    break;
            }
        }else {
            switch ($code) {
                case '200':
                    $msg = 'Message was delivered successfully';
                    break;
                
                case '1000':
                    $msg = 'A required field is missing';
                    break;

                case '1001':
                    $msg = 'Invalid IP Address';
                    break;

                case '1002':
                    $msg = 'Invalid Token"';
                    break;

                case '1003':
                case '1004':
                    $msg = 'Account Inactive';
                    break;

                case '1005':
                case '1006':
                    $msg = 'Account has been expired';
                    break;

                case '1007':
                    $msg = 'Invalid Receiver';
                    break;

                case '1008':
                    $msg = 'Invalid Sender';
                    break;

                case '1010':
                    $msg = 'Text cannot be empty';
                    break;

                case '1011':
                    $msg = 'No valid receiver';
                    break;

                case '1012':
                    $msg = 'No Credits Available';
                    break;

                case '1013':
                    $msg = 'Insufficient Credits';
                    break;

                default:
                    $msg = 'Unknown Error';
                    break;
            }
        }

        return $msg;
    }
} 
