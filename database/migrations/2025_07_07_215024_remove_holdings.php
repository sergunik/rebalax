<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('portfolio_holdings');
    }

    public function down(): void
    {
        // If you need to restore the table, you can define its structure here.
    }
};
