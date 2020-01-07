<?php

namespace App\LeaderRegistration\Http\Controllers;

use App\Common\Http\Controllers\BaseApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\LeaderRegistration\Repository\LeaderRegistrationRepository;
use Illuminate\Support\Facades\Mail;
use App\LeaderRegistration\Jobs\SendSms;
use App\LeaderRegistration\Jobs\SendWelcomeMail;

class LeaderRegistrationController extends BaseApiController
{

    protected $leaderRepo;

    public function __construct(LeaderRegistrationRepository $leaderRepo)
    {
        // set the leader repo
        $this->leaderRepo = $leaderRepo;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return $this->successResponse($this->leaderRepo->paginateLeaderLists($request, $this->getAuthUser()->id, 50), 'Leaders listing');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        try {

            $validator = Validator::make($request->all(),[
                'name'=>'required',
                'email'=>'required',
                'address'=>'required',
                'number'=>'required',
                'gender'=>'required',
            ]);

            if(!$validator->fails())
            {
                $reg = $this->leaderRepo->create($request);

                if(!$reg) throw new \Exception("Could not process", 1);
                $reg->setAttribute('site_logo', $this->getSetting('site_logo')['value']);
                dispatch(new SendSms($reg));
                dispatch(new SendWelcomeMail($reg));

                // send_email('registration', 'Leader Registration', $reg->email, $reg);
                // send_sms($reg);
                return $this->successResponse([], 'User added');

            }else {
                throw new \Exception($validator->errors()->first(), 1);
            }

        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 406);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $leaderId
     * @return \Illuminate\Http\Response
     */
    public function edit(int $leaderId)
    {
        try {
            if(!$leader = $this->leaderRepo->getLeader($leaderId)) throw new \Exception("Leader not found", 1);
            
            return $this->successResponse($leader, 'Leader detail');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 406);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
           $validator = Validator::make($request->all(),[
                'name'=>'required',
                'email'=>'required|unique:mysql2.audition_registration,email, ' . $request->id,
                'address'=>'required',
                'number'=>'required',
                'gender'=>'required',
            ]);

            if(!$validator->fails())
            {
                if(!$this->leaderRepo->update($request, $request->id)) throw new \Exception("Could not process", 1);

                return $this->successResponse([], 'Leader updated');

            }else{
                throw new \Exception($validator->errors()->first(), 1);
            }

        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 406);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Display a listing of unpaid leaders.
     *
     * @return \Illuminate\Http\Response
     */
    public function unpaidLeaders()
    {
        return $this->successResponse($this->leaderRepo->paginateUnpaidLeaders(50), 'Unpaid Leaders list');
    }
}
