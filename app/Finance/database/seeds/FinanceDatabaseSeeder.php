<?php

namespace App\Finance\database\seeds;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class FinanceDatabaseSeeder extends Seeder
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
