<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAtmPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('atm_prices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('bank_from');
            $table->json('bank_to');
            $table->json('network');
            $table->string('charge')->nullable()->default('N/A');
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('atm_prices');
    }
}
