<?php

namespace App\Contracts;

use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Interface BtcGainsCalculator
 *
 * @package App\Contracts
 */
interface BtcGainsCalculator
{
    /**
     * Set start date for savings
     *
     * @param Carbon $startDate
     * @return $this
     */
    public function setStartDate(Carbon $startDate): BtcGainsCalculator;

    /**
     * Set end date for savings
     *
     * @param Carbon $endDate
     * @return $this
     */
    public function setEndDate(Carbon $endDate): BtcGainsCalculator;

    /**
     * Set currency used
     *
     * @param string $currency
     * @return $this
     */
    public function setCurrency(string $currency): BtcGainsCalculator;

    /**
     * Set monthly saved dolar amount used
     *
     * @param float $monthlyDolarAmount
     * @return $this
     */
    public function setMonthlyDolarAmount(float $monthlyDolarAmount);

    /**
     * Get total saved in BTC
     *
     * @return float
     */
    public function getSavedTotalBtcAmount(): float;

    /**
     * Get total saved in Dolars
     *
     * @return float
     */
    public function getSavedTotalDolarAmount(): float;

    /**
     * Get gain percentage
     *
     * @return float
     */
    public function getGainPercentage(): float;

    /**
     * Get total invested amount in Dolars
     *
     * @return float
     */
    public function getTotalInvestedDolarAmount(): float;

    /**
     * Get detail per day of savings
     *
     * @return Collection
     */
    public function getDataPerDay(): Collection;
}