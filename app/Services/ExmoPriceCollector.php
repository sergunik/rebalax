<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\PriceCollector;
use App\Models\TokenPrice;
use Exception;
use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ExmoPriceCollector implements PriceCollector
{
    private const EXMO_API_URL = 'https://api.exmo.com/v1.1/ticker';

    private HttpClient $httpClient;

    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function collectPrices(): void
    {
        try {
            $tickerData = $this->fetchTickerData();
            $priceRecords = $this->transformTickerData($tickerData);
            $this->storePrices($priceRecords);
        } catch (Exception $e) {
            Log::error('Error processing Exmo ticker data', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    private function fetchTickerData(): array
    {
        $response = $this->httpClient
            ->timeout(5)
            ->retry(3, 100)

            ->asForm()
            ->withHeaders(['Content-Type' => 'application/x-www-form-urlencoded'])
            ->post(self::EXMO_API_URL);

        if ($response->failed()) {
            Log::error('Failed to fetch ticker data from Exmo API', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new RequestException($response);
        }
        return $response->json();
    }

    private function transformTickerData(array $tickerData): array
    {
        $priceRecords = [];
        $timestamp = Carbon::now();
        $hash = uniqid('', true);

        foreach ($tickerData as $pair => $data) {
            if (!$this->isValidPairData($data)) {
                continue;
            }

            $priceUsd = $this->extractUsdPrice($pair, $data);

            if ($priceUsd !== null) {
                $priceRecords[] = [
                    'symbol' => $this->extractSymbol($pair),
                    'pair' => $pair,
                    'price_usd' => $priceUsd,
                    'fetched_at' => $timestamp,
                    'fetch_hash' => $hash,
                ];
            }
        }

        return $priceRecords;
    }

    private function isValidPairData(array $data): bool
    {
        return isset($data['last_trade']) &&
            is_numeric($data['last_trade']) &&
            $data['last_trade'] > 0;
    }

    private function extractUsdPrice(string $pair, array $data): ?float
    {
        $symbol = explode('_', $pair);
        if (count($symbol) < 2) {
            return null; // Invalid pair format
        }

        if (
            in_array($symbol[0], config('rebalax.price_collector.skip'))
            || in_array($symbol[1], config('rebalax.price_collector.skip'))
        ) {
            return null;
        }

        // Check if the pair ends with a stablecoin
        if (!in_array($symbol[1], config('rebalax.stablecoins'))) {
            return null;
        }

        if (in_array($symbol[0], config('rebalax.stablecoins'))) {
            return null; // Skip stablecoin to stablecoin pairs
        }

        // Keep only pairs with USDT stablecoins
        if (!in_array($symbol[1], config('rebalax.price_collector.collect_only'))) {
            return null;
        }

        return (float) $data['last_trade'];
    }

    private function extractSymbol(string $pair): string
    {
        return explode('_', $pair)[0];
    }

    private function storePrices(array $priceRecords): void
    {
        if (empty($priceRecords)) {
            return;
        }

        TokenPrice::insert($priceRecords);
    }
}
