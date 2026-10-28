<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('streams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 140);
            $table->text('description')->nullable();
            $table->string('token_address', 120)->nullable();
            $table->string('token_symbol', 40)->nullable();
            $table->string('source', 20)->default('camera');
            $table->string('embed_url', 500)->nullable();
            $table->string('status', 20)->default('live');
            $table->unsignedInteger('viewers')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('streams');
    }
};
