<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('token_prices', function (Blueprint $table) {
            $table->string('fetch_hash', 23)->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('token_prices', function (Blueprint $table) {
            $table->dropColumn('fetch_hash');
        });
    }
};
