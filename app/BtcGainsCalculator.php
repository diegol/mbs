<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use App\Contracts\BtcGainsCalculator as BtcGainsCalculatorContract;
use App\Contracts\BtcConverter as BtcConverterContract;

/**
 * Class BTCGainsCalculator
 *
 * Given:
 *  - startDate
 *  - endDate
 *  - currency
 *  - monthlyDolarAmount
 *
 * This service calculates:
 *  - savedTotalBtcAmount
 *  - savedTotalDolarAmount (SavedTotalBtcAmount as the 'endDay' Dolar value)
 *  - gainPercentage
 *  - totalInvestedDolarAmount
 *  - dataPerDay (detail by day)
 *
 * @package App\Http\Controllers
 */
class BtcGainsCalculator implements BtcGainsCalculatorContract
{
    /**
     * If parameters are not set to be able to do the conversion
     */
    const ERR_PARAMETERS_NOT_SET = "Date or Currency are not set";


    /**
     * Currency to be used
     *
     * @var string
     */
    private $currency;

    /**
     * Monthly dolar amount saved
     *
     * @var float
     */
    private $monthlyDolarAmount;

    /**
     * BTC converter service
     *
     * @var BtcConverter
     */
    private $btcConverter;

    /**
     * Start date for the savings
     *
     * @var Carbon
     */
    private $startDate;

    /**
     * End date for the savings
     *
     * @var Carbon
     */
    private $endDate;

    /**
     * Detail data of savings per day
     *
     * @var Collection
     */
    private $dataPerDay;

    /**
     * Constructor parameters injected by ioc container
     *
     * @param BtcConverterContract $btcConverter
     */
    public function __construct(BtcConverterContract $btcConverter)
    {
        $this->btcConverter = $btcConverter;
    }

    /**
     * Set start date for savings
     *
     * @param Carbon $startDate
     * @return $this
     */
    public function setStartDate(Carbon $startDate): BtcGainsCalculatorContract
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Set end date for savings
     *
     * @param Carbon $endDate
     * @return $this
     */
    public function setEndDate(Carbon $endDate): BtcGainsCalculatorContract
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Set currency used
     *
     * @param string $currency
     * @return $this
     */
    public function setCurrency(string $currency): BtcGainsCalculatorContract
    {
        //[TODO] validate if it is a valid currency

        $this->currency = $currency;

        $this->btcConverter->setCurrency($currency);

        return $this;
    }

    /**
     * Set monthly saved dolar amount used
     *
     * @param float $monthlyDolarAmount
     * @return $this
     */
    public function setMonthlyDolarAmount(float $monthlyDolarAmount): BtcGainsCalculatorContract
    {
        $this->monthlyDolarAmount = $monthlyDolarAmount;

        return $this;
    }

    /**
     * Get total saved in BTC
     *
     * @return float
     */
    public function getSavedTotalBtcAmount(): float
    {
        return $this->getDataPerDay()->last()['savedBtcAmount'];
    }

    /**
     * Get total saved in Dolars
     *
     * @return float
     */
    public function getSavedTotalDolarAmount(): float
    {
        return $this->getDataPerDay()->last()['savedDolarAmount'];
    }

    /**
     * Get gain percentage
     *
     * @return float
     */
    public function getGainPercentage(): float
    {
        return $this->getDataPerDay()->last()['gainPercentage'];
    }

    /**
     * Get total invested amount
     *
     * @return float
     */
    public function getTotalInvestedDolarAmount(): float
    {
        return $this->getDataPerDay()->last()['investedDolarAmount'];
    }

    /**
     * Get detail per day of savings
     *
     * @return Collection
     */
    public function getDataPerDay(): Collection
    {
        /**
         * To avoid calculating it multiple times
         * if it was already calculated
         */
        if ($this->dataPerDay != null) {
            return $this->dataPerDay;
        }

        $date = $this->startDate;

        $this->dataPerDay = collect();
        $btcAmountTotal = 0;
        $investedDolarAmountTotal = 0;

        while ($date->lessThanOrEqualTo($this->endDate)) {

            $this->btcConverter->setDate($date);

            $btcAmountTotal           += $this->btcConverter->getBtcAmount($this->monthlyDolarAmount);

            $dolarAmountTotal          = $this->btcConverter->getDolarAmount($btcAmountTotal);

            $investedDolarAmountTotal += $this->monthlyDolarAmount;

            $this->dataPerDay->push([
                'date' => $date->toDateString(),
                //added additional information ,how much is the value on 1 BTC for the date
                'btcDolarValue' => round($this->btcConverter->getDolarAmount(1),2),
                'savedBtcAmount' => round($btcAmountTotal, 4),
                'savedDolarAmount' => round($dolarAmountTotal,2),
                'investedDolarAmount'=> round($investedDolarAmountTotal, 2),
                'gainPercentage' => round(($dolarAmountTotal - $investedDolarAmountTotal) /$investedDolarAmountTotal  * 100, 2),
            ]);

            $date->addWeek();
        }

        return $this->dataPerDay;
    }


    /**
     * Validate it parameters are set to be able to de the conversion
     *
     * @throws \Exception
     */
    private function validateParameters()
    {
        if ($this->startDate == null || $this->endDate == null || $this->currency == null || $this->monthlyDolarAmount) {
            //[TODO] add custom exception
            throw new \Exception(self::ERR_PARAMETERS_NOT_SET);
        }
    }
}

