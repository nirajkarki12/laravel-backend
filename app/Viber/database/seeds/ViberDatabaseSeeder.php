<?php

namespace App\Viber\database\seeds;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class ViberDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        // $this->call("OthersTableSeeder");
    }
}
