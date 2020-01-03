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

    protected $requestBody;

    protected $requestParams = array();

    public function __construct(array $params, $requestBody)
    {
        $this->requestParams = $params;
        $this->requestBody = $requestBody;

        foreach ($params as $propName => $propValue) {
            if (property_exists(get_class($this), $propName)) {
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
                    	$this->$propName = $propValue;
            			break;
            		}
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
    public function getRequestBody(){
        return $this->requestBody;
    }


}