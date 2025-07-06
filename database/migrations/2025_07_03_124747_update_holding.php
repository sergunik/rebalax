<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portfolio_holdings', function (Blueprint $table) {
            if (Schema::hasColumn('portfolio_holdings', 'user_id')) {
                $table->dropColumn('user_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('portfolio_holdings', function (Blueprint $table) {
            if (!Schema::hasColumn('portfolio_holdings', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('portfolio_id');
            }
        });
    }
};
