<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCurrencyPrices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('currency_historic_prices', function (Blueprint $table) {
            $table->increments('id');
            $table->date('date');
            /**
             * For the moment only USD
             */
            $table->enum('base_currency', ['usd']);
            $table->enum('quote_currency', ['nzd', 'aud', 'usd']);
            $table->decimal('price', 12, 2);

            //indexes
            $table->unique(['date', 'base_currency', 'quote_currency'], 'fx_dateBcQc');

            /**
             * [TODO] Check queries to add additional indexes
             */
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('currency_historic_prices');
    }
}
