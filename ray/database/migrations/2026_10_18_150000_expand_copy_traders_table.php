<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('copy_traders', function (Blueprint $table) {
            $table->string('slug', 90)->unique()->after('display_name');
            $table->string('tagline', 160)->nullable()->after('slug');
            $table->text('bio')->nullable()->after('tagline');
            $table->string('avatar_url', 300)->nullable()->after('bio');
            $table->string('banner_url', 300)->nullable()->after('avatar_url');
            $table->json('tags')->nullable()->after('banner_url');
            $table->string('risk_level', 10)->default('medium')->after('tags');
            $table->boolean('is_verified')->default(false)->after('risk_level');
            $table->boolean('is_featured')->default(false)->after('is_verified');
            $table->unsignedInteger('followers_count')->default(0)->after('is_featured');
            $table->unsignedInteger('trades_count')->default(0)->after('followers_count');
            $table->decimal('total_pnl_usd', 18, 2)->default(0)->after('trades_count');
            $table->decimal('roi_7d', 10, 2)->default(0)->after('total_pnl_usd');
            $table->decimal('roi_30d', 10, 2)->default(0)->after('roi_7d');
            $table->decimal('win_rate', 5, 2)->default(0)->after('roi_30d');
            $table->decimal('max_drawdown', 5, 2)->default(0)->after('win_rate');
            $table->decimal('volume_24h_usd', 18, 2)->default(0)->after('max_drawdown');
            $table->decimal('avg_trade_usd', 12, 2)->default(0)->after('volume_24h_usd');
            $table->json('stats_history')->nullable()->after('avg_trade_usd');
            $table->json('socials')->nullable()->after('stats_history');
            $table->index(['is_active', 'is_featured']);
            $table->index(['is_active', 'total_pnl_usd']);
            $table->index(['is_active', 'roi_30d']);
            $table->index(['is_active', 'win_rate']);
        });
    }

    public function down(): void
    {
        Schema::table('copy_traders', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'is_featured']);
            $table->dropIndex(['is_active', 'total_pnl_usd']);
            $table->dropIndex(['is_active', 'roi_30d']);
            $table->dropIndex(['is_active', 'win_rate']);
            $table->dropColumn([
                'slug', 'tagline', 'bio', 'avatar_url', 'banner_url', 'tags',
                'risk_level', 'is_verified', 'is_featured', 'followers_count',
                'trades_count', 'total_pnl_usd', 'roi_7d', 'roi_30d', 'win_rate',
                'max_drawdown', 'volume_24h_usd', 'avg_trade_usd', 'stats_history', 'socials',
            ]);
        });
    }
};
