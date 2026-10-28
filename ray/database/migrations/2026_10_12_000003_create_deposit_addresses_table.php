<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deposit_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('chain', 20)->default('sol');
            $table->string('address', 120);
            $table->string('label', 80)->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'chain', 'address']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deposit_addresses');
    }
};
