<?php
return [
    'stablecoins' => [
        'USD', // US Dollar
        'USDD', // US Dollar Digital
        'USDQ',
        'USDR',
        'USDT', //Tether USD
        'USDC', //USD Coin
        'BUSD', //Binance USD
        'DAI', //Dai Stablecoin
        'TUSD', //TrueUSD
    ],

    'price_collector' => [
        'collect_only' => [
            'USDT',
        ],
        'skip' => [
            'EUROE',
            'EURT',
        ],
    ],

    'rebalance' => [
        'simple' => [
            'enabled' => true,
            'threshold_percent' => 7.0,
            'batch_size' => 100,
            'timeout' => 20, // seconds
        ],
    ],
];
