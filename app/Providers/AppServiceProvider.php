<?php

namespace App\Providers;

use App\BtcConverter;
use App\BtcGainsCalculator;
use App\Contracts\BtcConverter as BtcConverterContract;
use App\Contracts\BtcGainsCalculator as BtcGainsCalculatorContract;
use App\Contracts\CurrencyConverter as UsdCurrencyConverterContract;
use App\CurrencyConverter;
use Illuminate\Support\ServiceProvider;

/**
 * Class AppServiceProvider
 *
 * @package App\Providers
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(BtcConverterContract::class, BtcConverter::class);

        $this->app->bind(BtcGainsCalculatorContract::class, BtcGainsCalculator::class);

        $this->app->bind(UsdCurrencyConverterContract::class, CurrencyConverter::class);

    }
}
