<?php 

namespace App\Viber\Events;

abstract class ViberEvent {

    /**
    * Event type
    *
    * @var string
    */
    protected $event;

    /**
     * Time of the event that triggered the callback
     *
     * @var integer
     */
    protected $timestamp;

    /**
     * Unique ID of the message
     *
     * @var string
     */
    protected $message_token;

    protected $requestParams = array();

    public function __construct(array $params)
    {
        $this->requestParams = $params;

        foreach ($params as $propName => $propValue) {
                switch ($propName) {
                    case 'sender':{
                        $this->sender = $propValue;
                        break;
                    }
                    case 'user':{
                        $this->user = $propValue;
                        break;
                    }                   
                    case 'message':{
                        $this->message = $propValue;
                        break;
                    }
                    default:{
                        return $this->$propName = $propValue;
                        break;
                    }
                }
        }
    }

    public function getEvent(){
        return $this->event;
    }

    public function getEventType(){
        return $this->event;
    }

    public function getTimestamp(){
        return $this->timestamp;
    }

    public function getMessageToken(){
        return $this->message_token;
    }

    public function getRequestParams(){
        return $this->requestParams;
    }

}