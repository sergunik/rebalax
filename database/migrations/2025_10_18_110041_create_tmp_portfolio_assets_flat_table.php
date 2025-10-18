<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tmp_portfolio_assets_flat', function (Blueprint $table) {
            $table->unsignedBigInteger('portfolio_id')->index();
            $table->unsignedTinyInteger('asset_count')->index();
            $table->string('asset_1', 20)->nullable();
            $table->string('asset_2', 20)->nullable();
            $table->string('asset_3', 20)->nullable();
            $table->string('asset_4', 20)->nullable();
            $table->string('asset_5', 20)->nullable();
            $table->string('asset_6', 20)->nullable();
            $table->string('asset_7', 20)->nullable();
            $table->string('asset_8', 20)->nullable();
            $table->string('asset_9', 20)->nullable();
            $table->string('asset_10', 20)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tmp_portfolio_assets_flat');
    }
};
