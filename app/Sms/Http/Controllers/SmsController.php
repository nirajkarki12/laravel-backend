<?php

namespace App\Sms\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Common\Http\Controllers\BaseApiController;
use App\Sms\Repository\SmsRepository;
use App\Sms\Jobs\ResendSms;

class SmsController extends BaseApiController
{

    protected $smsRepo;

    public function __construct(SmsRepository $smsRepo)
    {
        // set the sms repo
        $this->smsRepo = $smsRepo;
    }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(Request $request)
    {
        return $this->successResponse($this->smsRepo->paginateSmsLists($request, 50), 'Sms listing');
    }

    public function resendSms(Request $request)
    {
        $users = $this->smsRepo->getFilterData($request);
        
        foreach ($users as $user) {
            $user->sms_queue = 1;
            $user->update();
            dispatch(new ResendSms($user));
        }

        return $this->successResponse($this->smsRepo->paginateSmsLists($request, 50), 'Sms listing');
    }
}
