<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBtcPrices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('btc_historic_prices', function (Blueprint $table) {
            $table->increments('id');
            $table->date('date');
            $table->decimal('usdPrice', 12, 2);

            //indexes
            $table->unique('date');
        });

        /**
         * [TODO] Check queries to add additional indexes
         */
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('btc_historic_prices');
    }
}
