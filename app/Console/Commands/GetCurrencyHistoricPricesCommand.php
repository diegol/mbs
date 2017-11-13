<?php

namespace App\Console\Commands;

use App\Models\BtcHistoricPrice;
use App\Models\CurrencyHistoricPrice;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Get Historic currency prices using fixer.io api
 *
 * Fetch only currency prices for the dates missing in BtcHistoricPrice table
 * as we only need the currency prices for those dates
 *
 * Usage:
 *
 *  php artisan currency:historic-prices
 *  php artisan currency:historic-prices truncate (if we wanto to truncate table
 *
 * @package App\Console\Commands
 */
class GetCurrencyHistoricPricesCommand extends Command
{
    /**
     * Url from which we fetch btc market price
     *
     * [TODO] we could fetch other currencies, and this could be in a configuration
     * file if necesary, base currency only USD that strictly it is the only that is
     * needed for this project
     */
    const CURRENCY_PRICE_API = 'https://api.fixer.io/%s?base=USD&symbols=NZD,AUD';

    /**
     * Save Currency to the DB in bulk, 10 records
     */
    const COUNT_FOR_BULK_SAVING = 10;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currency:historic-prices {hasToTruncate=no}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get Currency Historic Market Prices';

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
        /**
         * In case that we want to fetch all currency prices again
         * (but the system wont work without this data unless is in USD)
         */
        $hasToTruncate = $this->argument('hasToTruncate');

        if (strtolower($hasToTruncate) == 'truncate') {
            CurrencyHistoricPrice::truncate();
        }

        $btcHistoricPriceDates      = BtcHistoricPrice::
            select('date')
            ->get()
            ->pluck('date');

        $currencyHistoricPriceDates = CurrencyHistoricPrice::
            select('date')
            ->get()
            ->pluck('date')
            ->unique();

        /**
         * We will fetch only currency prices for the dates missing in BtcHistoricPrice table
         * as we only need the currency prices for those dates
         */
        $missingDates = $btcHistoricPriceDates->diff($currencyHistoricPriceDates);

        /**
         * If empty no need to continue
         */
        if ($missingDates->count() == 0) {
            $this->comment("All Currency records already present, quitting..");
            return;
        }

        $this->comment("Save Currency Prices.");
        $this->saveCurrencyHistoricData($missingDates);


        $this->comment("Currency Prices inserted in the db.");

        /**
         * Clean cache has this will affect Gains calculator cached data
         */
        Cache::flush();

        $this->comment("Clean cache");
    }


    /**
     * Save Currency prices from the api
     *
     * @param int $months for how many months we want the data
     * @return array
     */
    private function saveCurrencyHistoricData(Collection $missingDates)
    {
        $client = new Client();

        $data = collect();
        $count = 0;
        foreach ($missingDates as $missingDate) {

            $this->comment("Date: " . $missingDate);

            $resultCurrency = $client->get(sprintf(self::CURRENCY_PRICE_API, $missingDate));
            sleep(1);
            if ($resultCurrency->getStatusCode() != 200) {
                $this->error("Ops, that should not happen.");
                return [];
            }

            $dataCurrency = json_decode($resultCurrency->getBody());

            $carbonDate = Carbon::createFromFormat('Y-m-d', $missingDate);

            //NZD currency
            $data->push([
                    'date'     => $carbonDate,
                    'base_currency'  => 'usd',
                    'quote_currency' => 'nzd',
                    'price'    => $dataCurrency->rates->NZD,
                ]
            );

            //AUD currency
            $data->push([
                    'date'           => $carbonDate,
                    'base_currency'  => 'usd',
                    'quote_currency' => 'aud',
                    'price'          => $dataCurrency->rates->AUD,
                ]
            );

            /**
             * insert data in bulk
             */
            if ($count++ == self::COUNT_FOR_BULK_SAVING) {
                $this->comment("Saving...");
                CurrencyHistoricPrice::insert($data->toArray());
                $data = collect();
                $count = 0;
            }
        }

    }
}
