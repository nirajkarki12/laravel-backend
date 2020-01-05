<?php

namespace App\Viber\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Viber\Models\Viber;

use App\Viber\Events\Conversation;
use App\Viber\Events\Subscribed;
use App\Viber\Events\Unsubscribed;
use App\Viber\Events\Message;
use App\Viber\Events\Delivered; 
use App\Viber\Events\Failed;  
use App\Viber\Events\Seen;

class WebhookController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(Request $request)
    {
      try {
      
  			$data = $request->all();

  			\Log::info($data);

  			if(!isset($data['event'])) throw new \Exception("Invalid Request", 1);

        switch ($data['event']) {
        	case Viber::CONVERSATION:{
            	event(new Conversation($data));
        		break;
        	}
        	case Viber::SUBSCRIBED:{
            	event(new Subscribed($data));
        		break;
        	}
        	case Viber::UNSUBSCRIBED:{
            	event(new Unsubscribed($data));
        		break;
        	}
        	case Viber::MESSAGE:{
            	event(new Message($data));
        		break;
        	}
          case Viber::DELIVERED:{
              event(new Delivered($data));
            break;
          }
          case Viber::SEEN:{
              event(new Seen($data));
            break;
          }
          case Viber::FAILED:{
              event(new Failed($data));
            break;
          }
        	
        	default:{
              throw new \Exception("Unknown Request.", 1);
          	break;
        	}
        }

        return new Response();

      } catch (\Exception $e) {
        return new Response($e->getMessage());      
      }
    }

    
}
