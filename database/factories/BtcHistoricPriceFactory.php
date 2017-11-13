<?php

use Faker\Generator as Faker;
use Carbon\Carbon;

$factory->define(App\Models\BtcHistoricPrice::class, function (Faker $faker) {

    return [
        'date' => Carbon::createFromFormat('Y-m-d', $faker->unique()->date('Y-m-d', 'now')),
        'usdPrice' => $faker->randomFloat(2,  0.01, 100000),
    ];
});
