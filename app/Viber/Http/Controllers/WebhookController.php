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

class WebhookController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(Request $request)
    {
			$data = $request->all();

			\Log::info($data);

			if(!isset($data['event'])) 
        	throw new \HttpException(400, "Invalid Request.");

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
      	
      	default:{
          	throw new \HttpException(400, "Invalid Request.");
          	break;
      	}
      }

      return new Response();
    }

    
}
