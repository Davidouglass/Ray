<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('copy_trader_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('follower_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('copy_trader_id')->constrained('copy_traders')->cascadeOnDelete();
            $table->decimal('fee_amount', 18, 9)->nullable();
            $table->decimal('paid_amount', 18, 9)->default(0);
            $table->string('status', 20)->default('active');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->unique(['follower_id', 'copy_trader_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('copy_trader_subscriptions');
    }
};