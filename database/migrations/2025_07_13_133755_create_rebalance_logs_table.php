<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rebalance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portfolio_id')->constrained('portfolios')->onDelete('cascade');
            $table->string('token_symbol', 20);
            $table->decimal('quantity_before', 36, 18);
            $table->decimal('quantity_after', 36, 18);
            $table->decimal('quantity_delta', 36, 18);
            $table->decimal('target_allocation_percent', 5, 2);
            $table->decimal('current_allocation_percent', 5, 2);
            $table->decimal('price_usd', 16, 8);
            $table->decimal('value_before_usd', 16, 8);
            $table->decimal('value_after_usd', 16, 8);
            $table->timestamp('executed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rebalance_logs');
    }
};
