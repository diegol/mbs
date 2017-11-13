<?php

namespace App\Http\Controllers;

use App\Http\Requests\BtcGainCalculatorApiRequest;
use App\Contracts\BtcGainsCalculator;
use Carbon\Carbon;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * ApiController for the BtcGainsCalculator
 *
 * @package App\Http\Controllers
 */
class ApiController extends BaseController
{
    //use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

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
     * API Request all the parameters are mandatory":
     *
     *  - months
     *  - currency
     *  - amount
     *
     * URI: [GET] api/v1/btc-gain-calculator/months/{months}/currency/{currency}/?amount={amount}
     *
     * The request is a GET as we are not changing any data
     * and as such is cachable (we are using CachePage middleware)
     *
     * @param BtcGainCalculatorApiRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(BtcGainCalculatorApiRequest $request, $months, $currency)
    {

        $monthlyDolarAmount = $request->get('amount');

        $this->setBtcGainsCalculatorParameters($months, $currency, $monthlyDolarAmount);

        /**
         * [TODO] use League\Fractal transaformers
         */
        return response()->json([
            'headers'                  => [
                'currency' => $currency,
                'months'   => $months,
                'amount'   => $monthlyDolarAmount,
            ],
            'data' => [
                'gainPercentage'           => $this->btcGainsCalculator->getGainPercentage(),
                'savedTotalBtcAmount'      => $this->btcGainsCalculator->getSavedTotalBtcAmount(),
                'savedTotalDolarAmount'    => $this->btcGainsCalculator->getSavedTotalDolarAmount(),
                'totalInvestedDolarAmount' => $this->btcGainsCalculator->getTotalInvestedDolarAmount(),
                'detailPerDay'             => $this->btcGainsCalculator->getDataPerDay(),
            ]
        ]);
    }

    /**
     * Set the request parameters into the BtcGainsCalculator
     *
     * @param int $months
     * @param string $currency
     * @param float $monthlyDolarAmount
     * @return $this
     */
    private function setBtcGainsCalculatorParameters(int $months, string $currency, float $monthlyDolarAmount): ApiController
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
