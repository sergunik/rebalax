<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('balances', function (Blueprint $table) {
            $table->id();
            $table->string('token_name', 8)->index();
            $table->decimal('amount', 30, 10);
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('balances');
    }
};
