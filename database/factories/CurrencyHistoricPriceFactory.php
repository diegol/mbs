<?php
use Faker\Generator as Faker;
use Carbon\Carbon;

$factory->define(App\Models\CurrencyHistoricPrice::class, function (Faker $faker) {

    return [
        'date' => Carbon::createFromFormat('Y-m-d', $faker->unique()->date('Y-m-d', 'now')),
        'base_currency' => $faker->randomElement(['usd']),
        'quote_currency' => $faker->randomElement(['nzd', 'aud', 'usd']),
        'price' => $faker->randomFloat(2,  0.01, 100000),
    ];
});
