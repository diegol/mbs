<?php

namespace App\Http\Controllers;

use App\Contracts\BtcGainsCalculator;
use App\Http\Requests\BtcGainCalculatorWebRequest;
use Carbon\Carbon;
use ConsoleTVs\Charts\Builder\Multi;
use ConsoleTVs\Charts\Facades\Charts;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\View;

/**
 * Shows the graph for the BTC Gain Calculator
 *
 *
 * @package App\Http\Controllers
 */
class WebController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Default saving Amount
     */
    const DEFAULT_SAVING_AMOUNT = 20;

    /**
     * Default period
     */
    const DEFAULT_MONTH_PERIOD = 6;

    /**
     * Default currency used
     */
    const DEFAULT_CURRENCY = 'nzd';


    /**
     * BtcGainsCalculator service
     *
     * @var BtcGainsCalculator
     */
    private $btcGainsCalculator;

    /**
     * Constructor parameters injected by ioc container
     *
     * @param BtcGainsCalculator $btcGainsCalculator
     */
    public function __construct(BtcGainsCalculator $btcGainsCalculator)
    {
        $this->btcGainsCalculator = $btcGainsCalculator;
    }


    /**
     * Web request, the paramenters are optional (and have defaults defined)
     *  - amount
     *  - months
     *  - currency
     *
     * The request is a GET as we are not changing any data
     * and as such is cachable (we are using CachePage middleware)
     *
     *
     * @param BtcGainCalculatorWebRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(BtcGainCalculatorWebRequest $request)
    {

        $monthlyDolarAmount = $request->get('amount', self::DEFAULT_SAVING_AMOUNT);
        $months             = $request->get('months', self::DEFAULT_MONTH_PERIOD);
        $currency           = $request->get('currency', self::DEFAULT_CURRENCY);

        $this->setBtcGainsCalculatorParameters($months, $currency, $monthlyDolarAmount);

        //[TODO] implement League\Fractal transformers
        return View::make('index', [
                'chart'                    => $this->getChart($currency),
                'gainPercentage'           => $this->btcGainsCalculator->getGainPercentage(),
                'savedTotalBtcAmount'      => $this->btcGainsCalculator->getSavedTotalBtcAmount(),
                'savedTotalDolarAmount'    => $this->btcGainsCalculator->getSavedTotalDolarAmount(),
                'totalInvestedDolarAmount' => $this->btcGainsCalculator->getTotalInvestedDolarAmount(),
                'currency'                 => $currency,
            ]
        );
    }

    /**
     * Create and return the chart instance
     *
     * @param string $currency
     * @return Multi
     */
    private function getChart(string $currency): Multi
    {
        return Charts::multi('line', 'highcharts')
            ->elementLabel(strtoupper($currency))
            ->title('BTC Gain Calculator')
            ->dimensions(1000, 500)
            ->legend(true)
            ->colors(['#ff0000', '#00ff00'])
            ->labels(['Saved Money', 'Btc Value'])
            ->dataset('Dolars Invested',
                $this->btcGainsCalculator->getDataPerDay()->pluck('investedDolarAmount')->toArray())
            ->dataset('Dolars Actual',
                $this->btcGainsCalculator->getDataPerDay()->pluck('savedDolarAmount')->toArray());
        //    ->dataset('Gain Percentage', $this->btcGainsCalculator->getDataPerDay()->pluck('gainPercentage')->toArray());

    }

    /**
     * Set the request parameters into the BtcGainsCalculator
     *
     * @param int $months
     * @param string $currency
     * @param float $monthlyDolarAmount
     * @return $this
     */
    private function setBtcGainsCalculatorParameters(int $months, string $currency, float $monthlyDolarAmount): WebController
    {
        $startDate = Carbon::now()->subMonth($months);

        /**
         * Added to the btcGainsCalculator 'endDate', then, in the future
         * could be easily added a feature where the user can set
         * start and end date then the user can explore historic data
         */
        $this->btcGainsCalculator
            ->setStartDate($startDate)
            ->setEndDate(Carbon::now())
            ->setCurrency($currency)
            ->setMonthlyDolarAmount($monthlyDolarAmount);

        return $this;
    }

}
