<?php
namespace App\Contracts;

use Carbon\Carbon;

/**
 * Interface BtcConverter
 *
 * @package App\Contracts
 */
interface BtcConverter
{

    /**
     * Set the currency used for the conversion
     *
     * @param string $currency
     * @return $this
     */
    public function setCurrency(string $currency): BtcConverter;

    /**
     * Set Date for the conversion
     *
     * @param Carbon $date
     * @return $this
     */
    public function setDate(Carbon $date): BtcConverter;

    /**
     * Get dolar amount from $btcAmount
     *
     * @param float $btcAmount
     * @return float
     */
    public function getDolarAmount(float $btcAmount): ?float;

    /**
     * Get btcAmount from dolar amount
     *
     * @param float $dolarAmount
     * @return float
     */
    public function getBtcAmount(float $dolarAmount): ?float;

}