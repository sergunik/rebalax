<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portfolio_assets', function (Blueprint $table) {
            $table->index(['portfolio_id', 'id'], 'idx_portfolio_assets_portfolio_id');
        });
    }

    public function down(): void
    {
        Schema::table('portfolio_assets', function (Blueprint $table) {
            $table->dropIndex('idx_portfolio_assets_portfolio_id');
        });
    }
};
