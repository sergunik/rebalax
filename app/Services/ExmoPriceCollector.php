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
        $lastTrade = (float) $data['last_trade'];

        if ($this->isUsdtPair($pair)) {
            return $lastTrade;
        }

        if ($this->isUsdPair($pair)) {
            return $lastTrade;
        }

        return null;
    }

    private function isUsdPair(string $pair): bool
    {
        return str_ends_with($pair, '_USD');
    }

    private function isUsdtPair(string $pair): bool
    {
        return str_ends_with($pair, '_USDT');
    }

    private function extractSymbol(string $pair): string
    {
        if ($this->isUsdPair($pair)) {
            return substr($pair, 0, -4);
        }

        if ($this->isUsdtPair($pair)) {
            return substr($pair, 0, -5);
        }

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
