<?php

namespace App\Viber\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Viber\Models\Viber;

use App\Viber\Events\Webhook;
use App\Viber\Events\Conversation;
use App\Viber\Events\Delivered;
use App\Viber\Events\Failed;
use App\Viber\Events\Seen;
use App\Viber\Events\Subscribed;
use App\Viber\Events\Unsubscribed;
use App\Viber\Events\Message;

class WebhookController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(Request $request)
    {
			$requestData = $data = $request->all();

			// if(!isset($data['event'])) 
   //      	throw new \HttpException(400, "Invalid Request.");

      switch ($data['event']) {
      	case Viber::WEBHOOK:{
          	event(new Webhook($data, $requestData));
      		break;
      	}
      	case Viber::CONVERSATION:{
          	event(new Conversation($data, $requestData));
      		break;
      	}
      	case Viber::DELIVERED:{
          	event(new Delivered($data, $requestData));
      		break;
      	}
      	case Viber::FAILED:{
          	event(new Failed($data, $requestData));
      		break;
      	}
      	case Viber::SEEN:{
          	event(new Seen($data, $requestData));
      		break;
      	}
      	case Viber::SUBSCRIBED:{
          	event(new Subscribed($data, $requestData));
      		break;
      	}
      	case Viber::UNSUBSCRIBED:{
          	event(new Unsubscribed($data, $requestData));
      		break;
      	}
      	case Viber::MESSAGE:{
          	event(new Message($data, $requestData));
      		break;
      	}
      	
      	default:{
          	// throw new HttpException(400, "Invalid Request.");
          	break;
      	}
      }

      return new Response();
    }

    
}
