<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class BtcHistoricPrice
 *
 * Used to store historic prices of btc in usd by date
 *
 * @package App\Models
 */
class BtcHistoricPrice extends Model
{
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
        'date', 'usdPrice'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    //accesors
}
