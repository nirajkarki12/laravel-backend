<?php

namespace App\LeaderRegistration\Repository;

use App\LeaderRegistration\Models\AdminAudition;
use App\Common\Repository\RepositoryInterface;
use Illuminate\Http\Request;
use App\LeaderRegistration\Models\LeaderRegistration;
use App\User\Models\User;
use Illuminate\Support\Facades\Hash;
use App\User\Http\Controllers\UserController;
use Log;
class LeaderRegistrationRepository implements RepositoryInterface
{
    // model property on class instances
    protected $leaderregistration;
    protected $adminAudition;
    protected $user;
    protected $authUser;

    public function __construct(LeaderRegistration $leaderregistration, User $user, AdminAudition $adminAudition, UserController $authUser) {
        $this->leaderregistration = $leaderregistration;
        $this->user = $user;
        $this->adminAudition = $adminAudition;
        $this->authUser = $authUser;
    }

    public function all() {
        return $this->leaderregistration->all();
    }

    public function create(Request $request) {
        $data=$request->all();

        $user=User::where('email',$request->email)->first();
        $this->leaderregistration=LeaderRegistration::where('email',$request->email)->first();
        $password='leaderAudition'.rand(1,100000);
        
        if(!$user)
        {
            $user=new User();
            $user->email=$request->email;
            $user->name=$request->name;
            $user->token=Hash::make(rand() . time() . rand());
            $user->token_expiry=time() + 24*3600*30;
            $user->device_token='';
            $user->is_activated = 1;
            $user->login_by = 'manual';
            $user->device_type = 'web';
            $user->social_unique_id ='';
            $user->address=$request->address;
            $user->mobile=$request->number;
            $user->gender=$request->gender;
            $user->password=Hash::make($password);
            if($request->has('image'))
            {
                $data['image'] = imageUpload($request->image,'/storage/app/public/leader/image');
                $user->picture=$data['image'];
            }else{
                $user->picture=null;
            }
    
            if(!$user->save())
            {
                throw new \Exception('User can not be created',1);
            }
        }

        $data['user_id']=$user->id;
        $data['payment_type']='offline';
        $data['channel']='offline';
        $data['country_code']='977';
        $data['registration_code']='LEADERSRBN'.$user->id;
        
        if(!$this->leaderregistration)
        {
            $reg=$this->leaderregistration->create($data);
        }
        else{
            $reg=$this->leaderregistration->update($data);
        }
       
        try {
            $this->adminAudition::create([
                'admin_id'=> $this->authUser->getUser()->id,
                'audition_id'=>$reg->id
            ]);
        } catch (\Throwable $th) {
            Log::debug($th->getMessage());
        }
        
        $reg->setAttribute('password',$password);
        
        return $reg;
    }

    public function update(Request $request, int $id) {
        $data = $request->all();
        $record = $this->leaderregistration->find($id);
        $user = User::where('id',$record->user_id)->first();

        if($request->has('image'))
        {
            $data['image'] = imageUpload($request->image,'/storage/app/public/leader/image');
            $user->picture = $data['image'];
        }

        $user->update($data);
        return $record->update($data);
    }

    public function delete(int $id) {
        return $this->leaderregistration->destroy($id);
    }

    public function show(int $id) {
        return $this->leaderregistration->findOrFail($id);
    }

    public function getModel() {
        return $this->leaderregistration;
    }

    public function setModel($leaderregistration) {
        $this->leaderregistration = $leaderregistration;
        return $this;
    }

    public function with($relations) {
        return $this->leaderregistration->with($relations);
    }

    public function getLeader(int $leaderId) {
        return $this->leaderregistration::where('id', $leaderId)
                ->first();
    }

    public function paginateLeaderLists(Request $request, int $userId, int $limit = 10) {
        return $this->leaderregistration::leftJoin('admin_auditions', 'admin_auditions.audition_id','=','audition_registration.id')
            ->where('payment_type', 'offline')
            ->where('name', 'like', $request->name.'%')
            ->where('number', 'like', $request->mobileNumber.'%')
            ->where('admin_auditions.admin_id', $userId)
            ->orderBy('audition_registration.created_at','desc')
            ->paginate($limit)
            ;
    }

    public function paginateUnpaidLeaders(int $limit = 10) {
        return $this->leaderregistration::orderBy('created_at','desc')
            ->where('payment_status', false)
            ->paginate($limit)
            ;
    }
}
