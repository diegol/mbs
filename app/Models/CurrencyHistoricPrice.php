<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CurrencyHistoricPrice
 *
 * Used to store currency prices, for the moment
 * the base currency only used is USD at the moment, then strictly speaking
 * this field is not necesary
 *
 * @package App\Models
 */
class CurrencyHistoricPrice extends Model
{
    /**
     * Invalid currency error
     */
    const ERR_INVALID_CURRENCY = 'Inavlide base currency';

    /**
     * Available currencies used at the moment (easily extensible)
     */
    const AVAILABLE_CURRENCIES = ['nzd', 'aud', 'usd'];

    /**
     * To disable updated/created_at
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'date', 'base_currency', 'quote_currency', 'price'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];



    //mutator example

    /**
     * Set Quote Currency, mutator example
     *
     * - set currency to lower case
     * - check if is between valid currencies
     *
     * @param  string  $value
     * @return CurrencyHistoricPrice
     */
    public function seQuoteCurrencyAttribute($value): CurrencyHistoricPrice
    {
        $currency = strtolower($value);

        if (!in_array($currency, self::AVAILABLE_CURRENCIES)) {
            //[TODO] custom Exception
            throw new Exception();
        }
        $this->attributes['quote_currency'] = $currency;

        return $this;
    }


    /**
     * Set Base Currency, mutator example
     *
     * - set currency to lower case
     * - check if is between valid currencies, only USD accepted
     * at the moment
     *
     * @param  string  $value
     * @return CurrencyHistoricPrice
     */
    public function setBaseCurrencyAttribute($value): CurrencyHistoricPrice
    {
        $currency = strtolower($value);

        if (!in_array($currency, self::AVAILABLE_CURRENCIES)) {
            //[TODO] custom Exception
            throw new Exception();
        }
        $this->attributes['base_currency'] = $currency;

        return $this;
    }
}
