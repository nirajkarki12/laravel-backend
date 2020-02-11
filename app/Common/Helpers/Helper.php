<?php
namespace App\Helpers;

use File;

class Helper
{

    public static function imageUplaod($file,$path='/app/public/user/post')
    {
        $name = rand(15,1500).time().'.'.$file->getClientOriginalExtension();
        $file->move(storage_path().$path, $name);
        return $name;
    }
    
    public static function imageDelete($file,$path='/app/public/user/post/')
    {
        $file=storage_path($path.$file);
        
        if(File::exists($file))
        {
            File::delete($file);
        }
    }
}