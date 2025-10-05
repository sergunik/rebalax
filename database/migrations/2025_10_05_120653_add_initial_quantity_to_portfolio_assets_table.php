<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portfolio_assets', function (Blueprint $table) {
            $table->decimal('initial_quantity', 36, 18)
                ->default(0.0)
                ->after('target_allocation_percent');
        });
    }

    public function down(): void
    {
        Schema::table('portfolio_assets', function (Blueprint $table) {
            $table->dropColumn('initial_quantity');
        });
    }
};
