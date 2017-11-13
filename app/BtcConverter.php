<?php

namespace App;

use App\Models\BtcHistoricPrice;
use Carbon\Carbon;
use App\Contracts\BtcConverter as BtcConverterContract;
use App\Contracts\CurrencyConverter as CurrencyConverterContract;
use Illuminate\Support\Facades\Cache;

/**
 * This class given a:
 *  - date and
 *  - currency
 *
 * Can convert:
 *
 *  - dolars in the given currency to btc
 *  - btc to dolars in the given currency
 *
 * @package App
 */
class BtcConverter implements BtcConverterContract
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
     * Currency used for the conversion
     *
     * @var string
     */
    private $currency;

    /**
     * Date for the conversion
     *
     * @var Carbon
     */
    private $date;

    /**
     * Collection with the historic prices
     *
     * @var Collection
     */
    private $btcHistoricPrices;

    /**
     * Currency converter
     *
     * @var CurrencyConverterContract
     */
    private $currencyConverter;

    /**
     * Constructor parameters injected by ioc container
     *
     * @param CurrencyConverterContract $currencyConverter
     */
    public function __construct(CurrencyConverterContract $currencyConverter)
    {
        /**
         * BTC amount prices are stored in USD
         */
        $this->currencyConverter = $currencyConverter->setBaseCurrency('USD');

        /**
         * Caching BTC historic prices
         *
         * Ideally Memcache or Redis should be implemented for the cache
         */
        $this->btcHistoricPrices = Cache::remember(
            'btcHistoricPrices',
            self::CACHE_TIME_HOURS * 60,
            function () {
                return BtcHistoricPrice::get()->keyBy('date');
            }
        );
    }

    /**
     * Set the currency used for the conversion
     *
     * @param string $currency
     * @return $this
     */
    public function setCurrency(string $currency): BtcConverterContract
    {
        //[TODO] validate if it is a valid currency

        $this->currency = strtolower($currency);

        $this->currencyConverter->setQuoteCurrency($this->currency);

        return $this;
    }

    /**
     * Set Date for the conversion
     *
     * @param Carbon $date
     * @return $this
     */
    public function setDate(Carbon $date): BtcConverterContract
    {
        $this->date = $date;

        $this->currencyConverter->setDate($date);

        return $this;
    }

    /**
     * Get dolar amount from $btcAmount
     *
     * @param float $btcAmount
     * @return float
     */
    public function getDolarAmount(float $btcAmount): ?float
    {
        $this->validateParameters();

        $btcHistoricPrice = $this->btcHistoricPrices->get($this->date->toDateString());

        /**
         * If it is no record to do the conversion
         * we return null
         */
        if ($btcHistoricPrice == null) {
            return null;
        }

        $usdAmount = $btcAmount * $btcHistoricPrice->usdPrice;

        $currencyAmount = $this->currencyConverter->getConvertedAmount($usdAmount);

        /**
         * In accounting we round 3 places
         * [TODO] check this
         */
        return round($currencyAmount, 3);
    }

    /**
     * Get btcAmount from dolar amount in the currency setted
     *
     * @param float $dolarAmount
     * @return float
     */
    public function getBtcAmount(float $currencyAmount): ?float
    {
        $this->validateParameters();

        $btcHistoricPrice = $this->btcHistoricPrices->get($this->date->toDateString());

        /**
         * If it is no record to do the conversion
         * we return null
         */
        if ($btcHistoricPrice == null) {
            return null;
        }

        $usdAmount = $this->currencyConverter->getBaseAmount($currencyAmount);


        /**
         * Smallest btc unit is 0.00000001
         * then round to 8 decimal places
         */
        return round($usdAmount / $btcHistoricPrice->usdPrice, 8);
    }

    /**
     * Validate it parameters are set to be able to de the conversion
     *
     * @throws \Exception
     */
    private function validateParameters()
    {
        if ($this->date == null || $this->currency == null) {
            //[TODO] custom exception
            throw new \Exception(self::ERR_PARAMETERS_NOT_SET);
        }
    }
}