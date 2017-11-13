<?php
namespace Tests\Unit;

use App\Models\CurrencyHistoricPrice;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Contracts\CurrencyConverter as CurrencyConverterContract;
use Faker\Factory;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class CurrencyConverterTest extends TestCase
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
     * and convert 100 random Dolars amounts to a Currency
     * for the existing dates created
     *
     * @test
     */
    public function validConversions()
    {

        $currencyHistoricPrices = factory(CurrencyHistoricPrice::class, 100)->create();

        $this->currencyConverter = app(CurrencyConverterContract::class)
            ->setBaseCurrency('usd');

        foreach ($currencyHistoricPrices as $currencyHistoricPrice) {

            $randomUsdAmount = $this->faker->randomFloat(2, 0, 1000);

            $actualCurrencyAmount =  $this->currencyConverter
                ->setQuoteCurrency($currencyHistoricPrice->quote_currency)
                ->setDate($currencyHistoricPrice->date)
                ->getConvertedAmount($randomUsdAmount);

            if ($currencyHistoricPrice->quote_currency == 'usd') {
                $expectedCurrencyAmount = $randomUsdAmount;
            } else {
                /**
                 * Round to 3 decimal places
                 */
                $expectedCurrencyAmount = round($randomUsdAmount * $currencyHistoricPrice->price, 3);
            }

            $this->assertEquals($expectedCurrencyAmount, $actualCurrencyAmount);
        }

    }

    /**
     * Try to convert Zero AUD to Currency
     * (Different base /quote currency)
     *
     * @test
     */
    public function convertZeroAud()
    {
        $currencyHistoricPrice = factory(CurrencyHistoricPrice::class)->create();
        $currencyHistoricPrice->quote_currency = 'aud';


        $actualCurrencyAmount =  app(CurrencyConverterContract::class)
            ->setBaseCurrency('usd')
            ->setQuoteCurrency($currencyHistoricPrice->quote_currency)
            ->setDate($currencyHistoricPrice->date)
            ->getConvertedAmount(0);

        $this->assertEquals(0, $actualCurrencyAmount);

    }

    /**
     * Try to convert Zero USD to Currency
     * (Same base /quote currency)
     *
     * @test
     */
    public function convertZeroUsd()
    {
        $currencyHistoricPrice = factory(CurrencyHistoricPrice::class)->create();
        $currencyHistoricPrice->quote_currency = 'usd';


        $actualCurrencyAmount =  app(CurrencyConverterContract::class)
            ->setBaseCurrency('usd')
            ->setQuoteCurrency($currencyHistoricPrice->quote_currency)
            ->setDate($currencyHistoricPrice->date)
            ->getConvertedAmount(0);

        $this->assertEquals(0, $actualCurrencyAmount);

    }


    /**
     * Try to convert a dolars to btc for a date that is not information
     *
     * @test
     */
    public function convertDolarsNoExistingRecord()
    {
        $currencyHistoricPrice = factory(CurrencyHistoricPrice::class)->create();
        $currencyHistoricPrice->quote_currency = 'nzd';

        $actualCurrencyAmount =  app(CurrencyConverterContract::class)
            ->setBaseCurrency('usd')
            ->setQuoteCurrency($currencyHistoricPrice->quote_currency)
            ->setDate($currencyHistoricPrice->date->addDay())
            ->getConvertedAmount(10);

        $this->assertNull($actualCurrencyAmount);

    }

    //[TODO] test
}