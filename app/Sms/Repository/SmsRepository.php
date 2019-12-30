<?php

namespace App\Sms\Repository;

use App\Common\Repository\RepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Sms\Models\SmsLog;

class SmsRepository implements RepositoryInterface
{
    // model property on class instances
    protected $smsLog;

    public function __construct(SmsLog $smsLog) {
        $this->smsLog = $smsLog;
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
        return $this->smsLog::leftJoin('audition_registration AS ar', 'ar.user_id','=','sms_logs.user_id')
            ->select(
                'country_code',
                'number',
                'registration_code',
                'type',
                'registration_code_send_count',
                'status',
                'message',
            )
            ->where('number', 'like', $request->number.'%')
            // ->where('number', 'like', $request->mobileNumber.'%')
            ->orderBy('sms_logs.created_at','desc')
            ->paginate($limit)
            ;
    }

}
