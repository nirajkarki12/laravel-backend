<?php

namespace App\User\database\seeds;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        DB::table('users')->insert([
            'name' => 'Niraj Karki',
            'username' => 'superadmin',
            'email' => 'nirajkarki12@gmail.com',
            'password' => bcrypt('123456'),
            'slug' => 'niraj-karki',
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        // $this->call("OthersTableSeeder");
    }
}
