<?php

namespace Tests\Feature;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Mockery;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * We want to test he ApiController, then we mock the service BtcGainsCalculator
 * as here we are not testing the service itslelf
 *
 * Basically in the controller we want to test based on the request parameters using
 * a mocked BtcGainsCalculator if we receive the expexted json output
 *
 *
 * @package Tests\Feature
 */
class ApiTest extends TestCase
{
    /**
     * Mock object
     *
     * @var Mockery\Mock
     */
    protected $btcGainsCalculatorMock;

    /**
     * Set up
     */
    public function setUp()
    {
        parent::setUp();

        /**
         * Mocking the BtcGainsCalculator
         */
        $this->btcGainsCalculatorMock = Mockery::mock('App\BtcGainsCalculator');

        $this->btcGainsCalculatorMock->shouldReceive('setCurrency')
            ->andReturn(Mockery::self());

        $this->btcGainsCalculatorMock->shouldReceive('setMonthlyDolarAmount')
            ->andReturn(Mockery::self());

        $this->btcGainsCalculatorMock->shouldReceive('setStartDate')
            ->andReturn(Mockery::self());


        $this->btcGainsCalculatorMock->shouldReceive('setEndDate')
            ->andReturn(Mockery::self());
    }

    /**
     * Tear Down
     */
    public function tearDown()
    {
        Mockery::close();
    }


    /**
     * A really basic test
     *
     * [TODO] a more realistic test
     *
     * @test
     */
    public function basic()
    {

        $this->btcGainsCalculatorMock->shouldReceive('getGainPercentage')
            ->once()
            ->andReturn(10);

        $this->btcGainsCalculatorMock->shouldReceive('getSavedTotalDolarAmount')
            ->once()
            ->andReturn(10);

        $this->btcGainsCalculatorMock->shouldReceive('getTotalInvestedDolarAmount')
            ->once()
            ->andReturn(10);

        $this->btcGainsCalculatorMock->shouldReceive('getSavedTotalBtcAmount')
            ->once()
            ->andReturn(10);

        $this->btcGainsCalculatorMock->shouldReceive('getDataPerDay')
            ->once()
            ->andReturn(
                new Collection(
                    [
                        [
                            'date' => 2016-05-11,
                            'btcDolarValue' => 0,
                            'savedBtcAmount' => 0,
                            'savedDolarAmount' => 0,
                            'investedDolarAmount' => 10000,
                            'gainPercentage' => -100,
                        ],
                    ]
                )
            );


        /**
         * Replacing the instance for the mock object
         */
        App::instance('App\BtcGainsCalculator', $this->btcGainsCalculatorMock);

        $uri = sprintf('api/v1/btc-gain-calculator/months/%d/currency/%s', 18, 'aud');

        /**
         * Doint the request to the api end point
         */
        $this->json('GET', $uri, ['amount' => 100])
            ->assertStatus(200)
            ->assertJson(
                [

                    "headers" => [
                        "currency" => "aud",
                        "months" => 18,
                        "amount" => 100,
                    ],
                    "data" => [
                        "gainPercentage" => 10,
                        "savedTotalBtcAmount" => 10,
                        "savedTotalDolarAmount" => 10,
                        "totalInvestedDolarAmount" => 10,
                        "detailPerDay" => [
                            [
                                "date" => 2000,
                                "btcDolarValue" => 0,
                                "savedBtcAmount" => 0,
                                "savedDolarAmount" => 0,
                                "investedDolarAmount" => 10000,
                                "gainPercentage" => -100
                            ],
                        ],
                    ]
                ]
            );



    }
}
