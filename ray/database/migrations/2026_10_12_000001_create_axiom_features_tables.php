<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tracked_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('chain', 20)->default('sol');
            $table->string('address', 120);
            $table->string('label', 80)->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'chain', 'address']);
        });

        Schema::create('connected_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('chain', 20)->default('sol');
            $table->string('address', 120);
            $table->string('label', 80)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->unique(['user_id', 'chain', 'address']);
        });

        Schema::create('watchlist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('chain', 20)->default('sol');
            $table->string('address', 120);
            $table->string('symbol', 40)->nullable();
            $table->string('name', 120)->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'chain', 'address']);
        });

        Schema::create('point_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 40);
            $table->integer('points');
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('referral_code', 16)->nullable()->unique()->after('email');
            $table->unsignedBigInteger('referred_by')->nullable()->after('referral_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('point_events');
        Schema::dropIfExists('watchlist_items');
        Schema::dropIfExists('connected_wallets');
        Schema::dropIfExists('tracked_wallets');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['referral_code', 'referred_by']);
        });
    }
};
