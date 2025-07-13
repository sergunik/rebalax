<?php

namespace Tests\Feature\Services;

use App\Contracts\PriceCollector;
use App\Models\TokenPrice;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExmoPriceCollectorTest extends TestCase
{
    use RefreshDatabase;

    private PriceCollector $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PriceCollector::class);
    }

    public function test_it_successfully_collects_and_stores_prices()
    {
        $mockResponse = [
            'BTC_USDT' => [
                'buy_price' => '50000.00',
                'sell_price' => '49900.00',
                'last_trade' => '49950.00',
                'high' => '51000.00',
                'low' => '49000.00',
                'avg' => '50000.00',
                'vol' => '100.00',
                'vol_curr' => '5000000.00',
                'updated' => 1234567890
            ],
            'ETH_USDT' => [
                'buy_price' => '3200.00',
                'sell_price' => '3190.00',
                'last_trade' => '3195.50',
                'high' => '3250.00',
                'low' => '3150.00',
                'avg' => '3200.00',
                'vol' => '500.00',
                'vol_curr' => '1600000.00',
                'updated' => 1234567890
            ],
            'LTC_BTC' => [
                'buy_price' => '0.0025',
                'sell_price' => '0.0024',
                'last_trade' => '0.00245',
                'high' => '0.0026',
                'low' => '0.0024',
                'avg' => '0.0025',
                'vol' => '1000.00',
                'vol_curr' => '2.45',
                'updated' => 1234567890
            ]
        ];

        Http::fake([
            'api.exmo.com/v1.1/ticker' => Http::response($mockResponse, 200)
        ]);

        $this->service->collectPrices();

        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://api.exmo.com/v1.1/ticker' &&
                $request->method() === 'POST' &&
                $request->header('Content-Type')[0] === 'application/x-www-form-urlencoded';
        });

        $this->assertDatabaseCount('token_prices', 2);

        $btcPrice = TokenPrice::where('symbol', 'BTC')->first();
        $this->assertNotNull($btcPrice);
        $this->assertEquals('49950.00000000', $btcPrice->price_usd);

        $ethPrice = TokenPrice::where('symbol', 'ETH')->first();
        $this->assertNotNull($ethPrice);
        $this->assertEquals('3195.50000000', $ethPrice->price_usd);

        $this->assertDatabaseMissing('token_prices', ['symbol' => 'LTC']);
    }

    public function test_it_handles_invalid_ticker_data()
    {
        $mockResponse = [
            'BTC_USDT' => [
                'buy_price' => '50000.00',
                'sell_price' => '49900.00',
                'high' => '51000.00',
                'low' => '49000.00'
            ],
            'ETH_USDT' => [
                'buy_price' => '3200.00',
                'sell_price' => '3190.00',
                'last_trade' => '0',
                'high' => '3250.00',
                'low' => '3150.00'
            ],
            'LTC_USDT' => [
                'buy_price' => '150.00',
                'sell_price' => '149.00',
                'last_trade' => '149.50',
                'high' => '155.00',
                'low' => '145.00'
            ]
        ];

        Http::fake([
            'api.exmo.com/v1.1/ticker' => Http::response($mockResponse, 200)
        ]);

        $this->service->collectPrices();

        $this->assertDatabaseCount('token_prices', 1);

        $ltcPrice = TokenPrice::where('symbol', 'LTC')->first();
        $this->assertNotNull($ltcPrice);
        $this->assertEquals('149.50000000', $ltcPrice->price_usd);
    }

    public function test_it_handles_empty_response()
    {
        Http::fake([
            'api.exmo.com/v1.1/ticker' => Http::response([], 200)
        ]);

        $this->service->collectPrices();

        $this->assertDatabaseCount('token_prices', 0);
    }

    public function test_it_throws_exception_on_api_error()
    {
        Http::fake([
            'api.exmo.com/v1.1/ticker' => Http::response(['error' => 'API Error'], 500)
        ]);

        $this->expectException(RequestException::class);

        $this->service->collectPrices();

        $this->assertDatabaseCount('token_prices', 0);
    }

    public function test_it_throws_exception_on_network_error()
    {
        Http::fake([
            'api.exmo.com/v1.1/ticker' => function () {
                throw new RequestException(
                    new Response(new \GuzzleHttp\Psr7\Response(500, [], 'Network Error'))
                );
            }
        ]);

        $this->expectException(RequestException::class);

        $this->service->collectPrices();

        $this->assertDatabaseCount('token_prices', 0);
    }

    public function test_it_inserts_multiple_records_in_single_query()
    {
        $mockResponse = [
            'BTC_USDT' => [
                'last_trade' => '50000.00'
            ],
            'ETH_USDT' => [
                'last_trade' => '3200.00'
            ],
            'ADA_USDT' => [
                'last_trade' => '0.45'
            ]
        ];

        Http::fake([
            'api.exmo.com/v1.1/ticker' => Http::response($mockResponse, 200)
        ]);

        $queryCount = 0;
        DB::flushQueryLog();
        DB::listen(function ($query) use (&$queryCount) {
            if (preg_match('/^insert into .?token_prices.? \(/', $query->sql)) {
                $queryCount++;
            }
        });

        $this->service->collectPrices();

        $this->assertEquals(1, $queryCount);
        $this->assertDatabaseCount('token_prices', 3);
    }

    public function test_it_correctly_extracts_symbols_from_different_pair_formats()
    {
        $mockResponse = [
            'BTC_USDT' => ['last_trade' => '50000.00'],
            'ETH_USDT' => ['last_trade' => '3200.00'],
            'DOGE_USDT' => ['last_trade' => '0.08'],
            'USDT_USD' => ['last_trade' => '1.00']
        ];

        Http::fake([
            'api.exmo.com/v1.1/ticker' => Http::response($mockResponse, 200)
        ]);

        $this->service->collectPrices();

        $this->assertDatabaseHas('token_prices', ['symbol' => 'BTC', 'price_usd' => '50000.00000000']);
        $this->assertDatabaseHas('token_prices', ['symbol' => 'ETH', 'price_usd' => '3200.00000000']);
        $this->assertDatabaseHas('token_prices', ['symbol' => 'DOGE', 'price_usd' => '0.08000000']);
    }
}
