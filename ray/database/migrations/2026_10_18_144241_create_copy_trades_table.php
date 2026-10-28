<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('copy_trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('copy_trader_id')->constrained('copy_traders')->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained('copy_trader_subscriptions')->nullOnDelete();
            $table->string('action', 10)->default('buy');
            $table->string('mint', 120);
            $table->string('symbol', 40)->nullable();
            $table->decimal('amount', 18, 9);
            $table->decimal('price', 18, 9);
            $table->decimal('total_usd', 18, 2)->nullable();
            $table->string('tx_signature', 200)->nullable();
            $table->timestamp('executed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('copy_trades');
    }
};