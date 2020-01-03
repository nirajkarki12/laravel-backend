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
          	dispatch(new Webhook($data, $requestData));
      		break;
      	}
      	case Viber::CONVERSATION:{
          	dispatch(new Conversation($data, $requestData));
      		break;
      	}
      	case Viber::DELIVERED:{
          	dispatch(new Delivered($data, $requestData));
      		break;
      	}
      	case Viber::FAILED:{
          	dispatch(new Failed($data, $requestData));
      		break;
      	}
      	case Viber::SEEN:{
          	dispatch(new Seen($data, $requestData));
      		break;
      	}
      	case Viber::SUBSCRIBED:{
          	dispatch(new Subscribed($data, $requestData));
      		break;
      	}
      	case Viber::UNSUBSCRIBED:{
          	dispatch(new Unsubscribed($data, $requestData));
      		break;
      	}
      	case Viber::MESSAGE:{
          	dispatch(new Message($data, $requestData));
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
