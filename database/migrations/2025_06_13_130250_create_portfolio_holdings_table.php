<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portfolio_holdings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portfolio_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('token_symbol', 20);
            $table->decimal('quantity', 28, 18);
            $table->timestamp('last_updated_at');
            $table->timestamps();

            // Ensure unique token per portfolio
            $table->unique(['portfolio_id', 'token_symbol']);

            // Critical indexes for cronjob performance
            $table->index(['portfolio_id', 'last_updated_at']);
            $table->index(['user_id', 'token_symbol']);
            $table->index('token_symbol');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolio_holdings');
    }
};
