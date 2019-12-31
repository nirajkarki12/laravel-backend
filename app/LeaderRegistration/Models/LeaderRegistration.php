<?php

namespace App\LeaderRegistration\Models;

use Illuminate\Database\Eloquent\Model;

class LeaderRegistration extends Model {

    protected $connection='mysql2';
    protected $table = 'audition_registration';
    // public $timestamps = false;
    protected $fillable = ['channel','registration_code','name','email','address','number','gender','image','payment_type','payment_status','user_id', 'country_code', 'registration_code_send_count','sms_queue'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'registration_code_send_count'
    ];

    public function setImageAttribute($image) {
    	$this->attributes['image'] = env('APP_URL') .'/storage/leader/image/' .$image;
    }
}
