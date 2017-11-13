<?php

namespace App;

use App\Models\CurrencyHistoricPrice;
use Carbon\Carbon;
use App\Contracts\CurrencyConverter as CurrencyConverterContract;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * This class convert USD to NZD ot AUD
 *
 * Given a:
 *  - date and
 *  - currency
 *
 * Can convert:
 *
 *  - usd to set currency
 *  - set currency to usd
 *
 * @package App
 */
class CurrencyConverter implements CurrencyConverterContract
{

    /**
     * Cache time in hours
     *
     * In reality this could be cached forever as cache is clear when we receive
     * new conversion data
     */
    const CACHE_TIME_HOURS = 2;

    /**
     * If parameters are not set to be able to do the conversion
     */
    const ERR_PARAMETERS_NOT_SET = "Date or Currency are not set";

    /**
     * Supported currencies
     */
    const SUPPORTED_CURRENCIES = ['nzd', 'aud'];

    /**
     * Currency used for the conversion
     *
     * @var string
     */
    private $quoteCurrency;


    /**
     * Base Currency used for the conversion
     * only USD supported right now
     *
     * @var string
     */
    private $baseCurrency;

    /**
     * Date for the conversion
     *
     * @var Carbon
     */
    private $date;

    /**
     * Set base currency used for the conversion
     *
     * [TODO] not implemented only convert from USD dolars
     * and strictly not needed for this project
     *
     * @param string $baseCurrency
     * @return CurrencyConverterContract
     */
    public function setBaseCurrency(string $baseCurrency): CurrencyConverterContract
    {
        //[TODO] validate if it is a valid currency

        $this->baseCurrency = $baseCurrency;

        /**
         * In case the the currency changes
         */
        $this->currencyHistoricPrices = null;

        return $this;
    }

    /**
     * Set quote currency used for the conversion
     *
     * @param string $quoteCurrency
     * @return CurrencyConverterContract
     */
    public function setQuoteCurrency(string $quoteCurrency): CurrencyConverterContract
    {
        //[TODO] validate if it is a valid currency

        $this->quoteCurrency = $quoteCurrency;

        /**
         * In case the the currency changes
         */
        $this->currencyHistoricPrices = null;

        return $this;
    }

    /**
     * Set Date for the conversion
     *
     * @param Carbon $date
     * @return CurrencyConverterContract
     */
    public function setDate(Carbon $date): CurrencyConverterContract
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get converted amount from $usdAmount
     *
     * @param float $usdAmount
     * @return float
     */
    public function getConvertedAmount(float $usdAmount): ?float
    {
        $this->validateParameters();

        if (strtolower($this->quoteCurrency) == 'usd') {
            return $usdAmount;
        }

        $currencyHistoricPrice = $this->getHistoricCurrencyData()->get($this->date->toDateString());

        /**
         * If it is no record to do the conversion
         * we return null
         */
        if ($currencyHistoricPrice == null) {
            return null;
        }

        /**
         * In accounting we round 3 places
         * [TODO] check this
         */
        return round($usdAmount * $currencyHistoricPrice->price, 3);
    }

    /**
     * Get base amount from $currencyAmount
     *
     * [TODO] check if this is correct as not sure if we can convert
     * in both direction as it should be a conversion fee
     *
     * @param float $currencyAmount
     * @return float
     */
    public function getBaseAmount(float $currencyAmount): ?float
    {
        $this->validateParameters();

        if (strtolower($this->quoteCurrency) == 'usd') {
            return $currencyAmount;
        }

        $currencyHistoricPrice = $this->getHistoricCurrencyData()->get($this->date->toDateString());

        /**
         * If it is no record to do the conversion
         * we return null
         */
        if ($currencyHistoricPrice == null) {
            return null;
        }

        /**
         * In accounting we round 3 places
         * [TODO] check this
         */
        return round($currencyAmount / $currencyHistoricPrice->price, 3);
    }

    /**
     * Query the DB getting all the data for the given currency
     *
     * (this assume that we will be usually querying the same currency
     * for the request) that is true for this project. It will work if is
     * not the case but will do potentially a lot of queries
     *
     *
     * @return Collection
     */
    private function getHistoricCurrencyData(): Collection
    {
        /**
         * Caching Currency historic prices
         *
         * Ideally Memcache or Redis should be implemented for the cache
         */
        $key = 'btcHistoricPrices-' . $this->quoteCurrency . '-' . $this->baseCurrency;

        return Cache::remember(
            $key,
            self::CACHE_TIME_HOURS * 60,
            function () {
                return CurrencyHistoricPrice::
                    where('quote_currency', $this->quoteCurrency)
                    ->where('base_currency', $this->baseCurrency)
                    ->get()
                    ->keyBy('date');
            }
        );
    }


    /**
     * Validate it parameters are set to be able to de the conversion
     *
     * @throws \Exception
     */
    private function validateParameters()
    {
        if ($this->date == null || $this->quoteCurrency == null || $this->baseCurrency == null) {
            throw new \Exception(self::ERR_PARAMETERS_NOT_SET);
        }
    }
}