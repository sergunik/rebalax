<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TokenPrice extends Model
{
    protected $fillable = [
        'symbol',
        'price_usd',
    ];

    protected $casts = [
        'price_usd' => 'decimal:8',
    ];
}
