<?php

namespace App\Console\Commands;

use App\Models\BtcHistoricPrice;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Get BTC historic prices using https://blockchain.info api
 *
 * Usage:
 *
 *   php artisan btc:historic-prices
 *
 * By default bring data from the last 18 months, but this
 * can be passed as a parameter:
 *
 *    php artisan btc:historic-prices 25
 *
 *
 * @package App\Console\Commands
 */
class GetBtcHistoricPricesCommand extends Command
{

    /**
     * Url from which we fetch btc market price
     */
    const BTC_MARKET_PRICE_API = 'https://blockchain.info/charts/market-price?timespan=%smonths&format=json';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'btc:historic-prices {months=18}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get Btc Historic Market Prices';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $months = $this->argument('months');

        /**
         * [TODO] we could change the code to no truncate the table
         * and insert missing data only
         * (as the other command GetCurrencyHistoricPrices already does)
         */
        BtcHistoricPrice::truncate();

        $this->comment("Getting Btc Prices.");

        $btcHistoricData = $this->getBtcHistoricData($months);

        /**
         * insert data in bulk
         */
        BtcHistoricPrice::insert($btcHistoricData);

        $this->comment("Btc Prices inserted in the db.");

        /**
         * After inserting latest Btc Prices we
         * run the command to get currencies in case that we have
         * now a date that is missing
         */
        $this->call('currency:historic-prices');

        /**
         * Clean cache has this will affect Gains calculator cached data
         */
        Cache::flush();
        $this->comment("Clean cache");
    }

    /**
     * Getting Btc prices from the api
     *
     * @param int $months for how many months we want the data
     * @return array
     */
    private function getBtcHistoricData(int $months): array
    {
        $client = new Client();
        $resultBtc = $client->get(sprintf(self::BTC_MARKET_PRICE_API, $months));

        if ($resultBtc->getStatusCode() != 200) {
            $this->error("Ops, that should not happen.");
            return [];
        }
        $dataBtc =  json_decode($resultBtc->getBody());

        $this->comment("Btc API Prices count " . count($dataBtc->values));

        $data = collect();
        foreach ($dataBtc->values as $value)
        {
            $date = Carbon::createFromTimestamp($value->x);

            $data->push([
                'date' => $date,
                'usdPrice' => $value->y,
            ]);
        }

        return $data->toArray();
    }
}
