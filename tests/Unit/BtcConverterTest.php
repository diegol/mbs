<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\BtcHistoricPrice;
use App\Contracts\BtcConverter as BtcConverterContract;
use Faker\Factory;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class BtcConverterTest extends TestCase
{
    /**
     * Drop DB and create it from migration for each test
     */
    use DatabaseMigrations;


    /**
     * @var \Faker\Generator
     */
    private $faker;


    /**
     *
     * @inheritdoc
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->faker = Factory::create();
    }

    /**
     * Create 100 Historic Prices
     * and convert 100 random Dolars amounts to Btc
     * for the existing dates created
     *
     * @test
     */
    public function validConversions()
    {

        $btcHistoricPrices = factory(BtcHistoricPrice::class, 100)->create();

        $btcConverter = app(BtcConverterContract::class);

        foreach ($btcHistoricPrices as $btcHistoricPrice) {
            $randomDolarAmount =  $this->faker->randomFloat(2, 0.01, 1000);

            $actualBtcAmount =  $btcConverter
               ->setCurrency('usd')
               ->setDate($btcHistoricPrice->date)
               ->getBtcAmount($randomDolarAmount);

            /**
             * Smallest btc unit is 0.00000001
             * then round to 8 decimal places
             */
            $expectedBtcAmount = round($randomDolarAmount / $btcHistoricPrice['usdPrice'], 8);

            $this->assertEquals($expectedBtcAmount, $actualBtcAmount);
        }

    }


    /**
     * Try to convert Zero dolars to Btc
     *
     * @test
     */
    public function convertZeroDolars()
    {

        $btcHistoricPrice = factory(BtcHistoricPrice::class)->create();

        $btcConverter = app(BtcConverterContract::class);

        $actualBtcAmount =  $btcConverter
            ->setCurrency('usd')
            ->setDate($btcHistoricPrice->date)
            ->getBtcAmount(0);


        $this->assertEquals(0, $actualBtcAmount);

    }


    /**
     * Try to convert a dolars to btc for a date that is not information
     *
     * @test
     */
    public function convertDolarsNoExistingRecord()
    {

        $btcHistoricPrice = factory(BtcHistoricPrice::class)->create();

        $btcConverter = app(BtcConverterContract::class);

        $actualBtcAmount =  $btcConverter
            ->setCurrency('usd')
            ->setDate($btcHistoricPrice->date->addDay())
            ->getBtcAmount(10);

        $this->assertNull($actualBtcAmount);

    }


    /**
     * [TODO] Similar test for convertion from Btc to Dolars
     */
}
