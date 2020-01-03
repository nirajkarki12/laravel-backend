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
			$requestData = $request->all();
			$data = json_decode($requestData, true);
			return $requestData;
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
          	$curl = curl_init();

						curl_setopt_array($curl, array(
						  CURLOPT_URL => "https://chatapi.viber.com/pa/send_message",
						  CURLOPT_RETURNTRANSFER => true,
						  CURLOPT_ENCODING => "",
						  CURLOPT_MAXREDIRS => 10,
						  CURLOPT_TIMEOUT => 30,
						  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
						  CURLOPT_CUSTOMREQUEST => "POST",
						  CURLOPT_POSTFIELDS => "{\n   \"receiver\":\"PIaAAXFD3ORtQqh/KG9XdQ==\",\n   \"min_api_version\":1,\n   \"sender\":{\n      \"name\":\"John McClane\",\n      \"avatar\":\"http://avatar.example.com\"\n   },\n   \"tracking_data\":\"tracking data\",\n   \"type\":\"text\",\n   \"text\":\"Hello world!\"\n}",
						  CURLOPT_HTTPHEADER => array(
						    "content-type: application/json",
						    "x-viber-auth-token: 4ad7c1c218e7d728-e92ed8f87672532e-5bdac0ddf6641518"
						  ),
						));

						$response = curl_exec($curl);
						$err = curl_error($curl);

						curl_close($curl);

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
