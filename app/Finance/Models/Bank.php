<?php

namespace App\Finance\Models;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $connection = 'mysql';
    protected $fillable = ['name', 'logo', 'abbre', 'logo_full_path'];

    public function setLogoFullPathAttribute($logo) {
    	$this->attributes['logo_full_path'] = env('APP_URL') .'/storage/bank/logo/' .$logo;
    }

    public function setNameAttribute($name) {
    	$this->attributes['name'] = strtoupper($name);
    }

    public function setAbbreAttribute($abbre) {
    	$this->attributes['abbre'] = strtoupper($abbre);
    }
}
