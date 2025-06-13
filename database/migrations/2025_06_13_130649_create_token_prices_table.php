<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('token_prices', function (Blueprint $table) {
            $table->id();
            $table->string('symbol', 20);
            $table->decimal('price_usd', 16, 8);
            $table->timestamps();

//            // For fast price lookups during rebalancing
//            $table->unique(['symbol', 'fetched_at']);
//            $table->index(['symbol', 'fetched_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('token_prices');
    }
};
