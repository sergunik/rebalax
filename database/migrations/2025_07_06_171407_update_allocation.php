<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portfolio_allocations', function (Blueprint $table) {
            $table->decimal('target_allocation_percent', 5, 2)->change();
        });
    }

    public function down(): void
    {
        Schema::table('portfolio_allocations', function (Blueprint $table) {
            $table->decimal('target_allocation_percent', 5, 4)->change();
        });
    }
};
