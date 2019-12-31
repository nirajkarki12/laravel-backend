<?php

namespace App\Sms\Repository;

use App\Common\Repository\RepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Sms\Models\SmsLog;
use App\LeaderRegistration\Models\LeaderRegistration;

class SmsRepository implements RepositoryInterface
{
    // model property on class instances
    protected $smsLog;
    protected $audition;

    public function __construct(SmsLog $smsLog, LeaderRegistration $audition) {
        $this->smsLog = $smsLog;
        $this->audition = $audition;
    }

    public function all() {
        return $this->smsLog->all();
    }

    public function create(Request $request) {
        //
    }

    public function update(Request $request, int $id) {
        //
    }

    public function delete(int $id) {
        return $this->smsLog->destroy($id);
    }

    public function show(int $id) {
        return $this->smsLog->findOrFail($id);
    }

    public function getModel() {
        return $this->smsLog;
    }

    public function setModel($smsLog) {
        $this->smsLog = $smsLog;
        return $this;
    }

    public function with($relations) {
        return $this->smsLog->with($relations);
    }

    public function getLeader(int $smsId) {
        return $this->smsLog::where('id', $smsId)
                ->first();
    }

    public function paginateSmsLists(Request $request, int $limit = 10) {

        $sql = $this->smsLog::leftJoin('audition_registration AS ar', 'ar.user_id','=','sms_logs.user_id')
            ->select(
                'ar.name',
                'country_code',
                'number',
                'registration_code',
                'type',
                'registration_code_send_count',
                'status',
                'response_code',
                'ar.sms_queue',
                \DB::raw('(CASE WHEN ar.sms_queue = 0 THEN message ELSE "SMS is in Queue" END) AS message'),
                'request',
                'sms_logs.created_at'
            )
            ->where('ar.name', 'like', $request->name.'%')
            ->where('ar.number', 'like', $request->number.'%')
            ->where('ar.country_code', 'like', $request->countryCode.'%')
            ->where('ar.registration_code', 'like', $request->registrationCode.'%')
            ->where('sms_logs.response_code', 'like', $request->responseCode.'%')
            ->whereIn('sms_logs.id', function($q){
                $q->select(\DB::raw('MAX(id) FROM sms_logs GROUP BY user_id'));
            })
            ->groupBy('ar.user_id')
            ->orderBy('sms_logs.created_at','desc')
            ;

        if($request->status){
            switch ($request->status) {
                case 'success':
                    $sql->where('sms_logs.status', 1);
                    break;

                case 'failure':
                    $sql->where('sms_logs.status', 0);
                    break;

                case 'queue':
                    $sql->where('ar.sms_queue', 1)
                        ;
                    break;
            }
        }

        if($request->network){
            $sql->where('sms_logs.type', $request->network);
        }

        return $sql->paginate($limit);
            
    }

    public function getFilterData(Request $request) {
        $sql = $this->audition::leftJoin('sms_logs AS sl', 'audition_registration.user_id','=','sl.user_id')
            ->select(
                'audition_registration.*',
            )
            ->where('audition_registration.name', 'like', $request->name.'%')
            ->where('audition_registration.number', 'like', $request->number.'%')
            ->where('audition_registration.country_code', 'like', $request->countryCode.'%')
            ->where('audition_registration.registration_code', 'like', $request->registrationCode.'%')
            ->where('sl.response_code', 'like', $request->responseCode.'%')
            ->groupBy('audition_registration.user_id')
            ;

        if($request->status){
            switch ($request->status) {
                case 'success':
                    $sql->where('sl.status', 1);
                    break;

                case 'failure':
                    $sql->where('sl.status', 0);
                    break;
            }
        }

        if($request->network){
            $sql->where('sl.type', $request->network);
        }

        return $sql->get();
    }

}
