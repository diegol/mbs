<?php

namespace App\Contracts;

use Carbon\Carbon;

interface CurrencyConverter
{


    /**
     * Set base currency used for the conversion
     *
     * [TODO] not implemented only convert from USD dolars
     * and strictly not needed for this project
     *
     * @param string $baseCurrency
     * @return CurrencyConverter
     */
    public function setBaseCurrency(string $baseCurrency): CurrencyConverter;

    /**
     * Set the currency used for the conversion
     *
     * @param string $quoteCurrency
     * @return CurrencyConverter
     */
    public function setQuoteCurrency(string $quoteCurrency): CurrencyConverter;

    /**
     * Set Date for the conversion
     *
     * @param Carbon $date
     * @return CurrencyConverter
     */
    public function setDate(Carbon $date): CurrencyConverter;

    /**
     * Get converted amount from $usdAmount
     *
     * @param float $usdAmount
     * @return float
     */
    public function getConvertedAmount(float $usdAmount): ?float;

    /**
     * Get base amount from $currencyAmount
     *
     * @param float $currencyAmount
     * @return float
     */
    public function getBaseAmount(float $currencyAmount): ?float;

}