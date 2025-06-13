<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portfolio_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portfolio_id')->constrained()->onDelete('cascade');
            $table->string('token_symbol', 20);
            $table->decimal('target_allocation_percent', 5, 4);
            $table->timestamps();

            // Ensure unique token per portfolio
            $table->unique(['portfolio_id', 'token_symbol']);

            $table->index('token_symbol');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolio_allocations');
    }
};
