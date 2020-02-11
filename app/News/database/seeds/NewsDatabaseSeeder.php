<?php

namespace App\News\database\seeds;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class NewsDatabaseSeeder extends Seeder
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
