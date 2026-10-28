<?php

namespace Database\Seeders;

use App\Models\CopyTrade;
use App\Models\CopyTrader;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CopyTraderSeeder extends Seeder
{
    private const NAMES = [
        ['SolSniper', 'Precision sniper on Solana memes', 'low', true, true],
        ['DiamondHandsDan', 'Long-term conviction plays only', 'low', true, false],
        ['MoonshotMara', 'Early entry degen, exits clean', 'high', true, true],
        ['LiquidityLucas', 'LP-focused strategy, steady yield', 'low', false, false],
        ['PumpFiPaul', 'Pump.fun graduation hunter', 'high', true, false],
        ['TrenchWarrior', 'Lives in the trenches, 100+ trades/day', 'high', false, false],
        ['AlphaAva', 'On-chain alpha, wallets tracked', 'medium', true, true],
        ['ChartChad', 'Technical analysis + momentum', 'medium', true, false],
        ['WhaleWatcherWes', 'Follows whale wallets, copy mirrors', 'medium', false, false],
        ['GemHunterGina', 'Low-cap gems before they trend', 'high', true, false],
        ['SteadyEddie', 'Conservative sizing, high win-rate', 'low', true, false],
        ['DegenDiana', 'High risk high reward degen flips', 'high', false, false],
        ['SolMaxiSam', 'SOL ecosystem maximalist', 'medium', true, false],
        ['ArbHunterArt', 'Cross-DEX arbitrage specialist', 'low', false, false],
        ['MemeKingMarcus', 'Meme coin portfolio rotation', 'high', true, false],
        ['RiskManagerRita', 'Strict stop-losses, risk parity', 'low', true, false],
        ['FlowTraderFelix', 'Order-flow reading, scalp trades', 'medium', false, false],
        ['NewPairNina', 'Fresh pair sniping, exit fast', 'high', false, false],
        ['VolumeVince', 'Volume spike momentum plays', 'medium', true, false],
        ['TrendTraderTara', 'Swing trades on trending tokens', 'medium', true, false],
        ['QuietWhaleQuinn', 'Silent accumulation, big bags', 'low', true, false],
        ['BlitzBuyerBoris', 'Millisecond execution edge', 'high', false, false],
        ['MacroMike', 'Macro-driven rotating bags', 'low', false, false],
        ['YieldYara', 'Yield farming + airdrop farming', 'low', true, false],
        ['RocketRyan', '100x chaser, occasional hits', 'high', false, false],
        ['SafeTraderSofia', 'Blue-chip Solana only', 'low', true, false],
        ['PulsePete', 'Reads pulse feed first', 'medium', false, false],
        ['BundleBusterBen', 'Detects bundles, avoids rugs', 'medium', true, false],
        ['MomentumMaya', 'Rides momentum, cuts losers', 'medium', true, false],
        ['CautiousChris', 'Half-size entries, scaled exits', 'low', false, false],
        ['ApeAndExitAlex', 'Ape early, exit early', 'high', false, false],
        ['SniperNestor', 'Limit-order sniper, patient', 'low', true, false],
        ['CycleSurferCam', 'Trades funding cycles', 'medium', false, false],
        ['LadderLena', 'Ladder entries, grid exits', 'low', false, false],
        ['DipBuyerDerek', 'Buys every dip, holds forever', 'medium', false, false],
        ['ScalpQueenSasha', '15-second scalps, thousands weekly', 'high', true, false],
        ['RotationRicky', 'Sector rotation specialist', 'medium', false, false],
        ['InsiderIntelIvy', 'Tracks smart money wallets', 'medium', true, false],
        ['KellyCriterionKarl', 'Optimal bet sizing, math-driven', 'low', true, false],
        ['MoonMoonMorgan', 'Only moonshots, zero fear', 'high', false, false],
        ['PennyPixelPablo', 'Sub-$1M mcap hunter', 'high', false, false],
        ['SurfTheWaveSam', 'Wave theory on 4H charts', 'medium', false, false],
        ['HedgeHeroHana', 'Hedged positions, market neutral', 'low', true, false],
        ['FastFingerFinn', 'Keyboard sniper, no hesitation', 'high', false, false],
        ['PortfolioPia', 'Diversified 20-bag portfolio', 'low', true, false],
        ['CatalystCarl', 'Event-driven entries', 'medium', false, false],
        ['ContrarianCleo', 'Buys fear, sells greed', 'medium', true, false],
        ['LiquidityLurkerLiam', 'Watches LP depth before entry', 'low', false, false],
        ['GammaGamblerGus', 'Gamma exposure plays', 'high', false, false],
        ['NarrativeNora', 'Trades narratives, not charts', 'medium', true, false],
        ['SupportSamir', 'Bounce trader at key levels', 'medium', false, false],
        ['VolumeProfileVera', 'Volume profile + POC entries', 'low', true, false],
        ['BundleBrian', 'Anti-bundle detection pro', 'medium', false, false],
        ['EarlyBirdElena', 'First 5 minutes of launches', 'high', true, false],
        ['TrendFollowingTheo', 'Pure trend-following system', 'medium', true, false],
        ['MicroCapMilo', 'Micro-cap specialist, 50+ bags', 'high', false, false],
        ['HODLerHugo', 'Never sells, up 400% unrealized', 'low', false, false],
        ['BreakoutBianca', 'Breakout + volume confirmation', 'medium', true, false],
        ['RangeRangerReed', 'Range-bound mean reversion', 'low', false, false],
        ['SentimentSam', 'Social sentiment scoring', 'medium', false, false],
        ['WhaleFlipWalter', 'Follows whale buys, flips fast', 'high', true, false],
        ['StakingStella', 'Staking + trading hybrid', 'low', false, false],
        ['PerpPilotParker', 'Perps and spot hedging', 'medium', true, false],
        ['AlphaWaveArjun', 'On-chain data + alpha waves', 'medium', true, false],
    ];

    private const TAG_POOL = [
        'Sniper', 'Swing', 'Scalp', 'Memes', 'DeFi', 'Blue-chip', 'Low-cap',
        'Momentum', 'Arbitrage', 'Yield', 'Whale-follow', 'Trenches',
        'Technical', 'On-chain alpha', 'Bundles', 'Launches', 'Perps', 'NFT',
    ];

    private const TOKENS = [
        ['SOL', 'So11111111111111111111111111111111111111112', 140, 260],
        ['BONK', 'DezXAZ8z7PnrnRJjz3wXBoRgixCa6xjnB7YaB1pPB263', 0.000012, 0.000032],
        ['WIF', 'EKpQGSJtjMFqKZ9KQanSqYXRcF8fBopzLHYxdM65zcjm', 0.45, 1.8],
        ['JUP', 'JUPyiwrYJFskUPiHa7hkeR8VUtAeFoSYbKedZNsDvCN', 0.28, 0.9],
        ['JTO', 'jtojtomepa8beP8AuQc6eXt5FiJtFMVQsKoZzE9Kpk', 1.1, 3.4],
        ['PYTH', 'HZ1JovNiVvGrGNiiYvEozEVgZ58xaU3RKwX8eACQBCt3', 0.18, 0.55],
        ['RAY', '4k3Dyjzvzp8eMZWUXbBCjEvwSkkk59S5iCNLY3QrkX6R', 0.8, 2.6],
        ['ORCA', 'orcaEKTdK7LKz57vaAYr9QeNsVEPfiu6QeMU1kektZE', 0.35, 1.4],
        ['W', '8pCwSog6z3ep3vKgzjAdtAqN2hU3sDBkRdWWjNzHfGH', 0.14, 0.6],
        ['DRIFT', 'DriFtubJdXCDiH2S2j3Wrcbtk5dnVXYL6pceFSLdpump', 0.35, 1.2],
        ['POPCAT', '7GCihgDB8fe6KNjn2MYtkzZcRjQy3t9GHdC8uHYmW2hr', 0.15, 0.95],
        ['PNUT', '2qEHjDLDLbuBgRYvsxhc5D6uDWAivNFZGan56P1tpump', 0.12, 0.9],
        ['FARTCOIN', '9BB6NFEcjBCtnNLFko2FqVQBq8HHM13kCyYcdQbgpump', 0.03, 0.25],
        ['GIGA', '63LfDmNb3MQ8mw9MtZ2To9bEA2M71kZUUGq5tiJxcqj9', 0.008, 0.06],
        ['MOODENG', 'ED5nyyWEzpPPiWimP8vYm7sD7TD3LAt3Q3gRTWHzPJBY', 0.05, 0.35],
        ['RENDER', 'rndrizKT3MKHFfiEzaVzGjHrbj2gWQnLvrQLdvBbTFF', 2.4, 6.8],
        ['HNT', 'HtZDuTQo2eTA4ZgTCNuwqtvMd8VeZzmKJh7kRBhJEwtM', 1.8, 5.2],
        ['AI16Z', 'HeLp6NuQkmYB4pYWo2zYs22mESHXPQYzXbB8n4V98jwC', 0.015, 0.14],
    ];

    public function run(): void
    {
        $owner = User::orderBy('id')->first();
        if (!$owner) {
            return;
        }

        mt_srand(42);

        foreach (self::NAMES as $i => [$name, $tagline, $risk, $verified, $featured]) {
            $slug = Str::slug($name);
            if (CopyTrader::where('slug', $slug)->exists()) {
                continue;
            }

            $winRate = match ($risk) {
                'low' => mt_rand(6200, 8400) / 100,
                'medium' => mt_rand(4500, 6800) / 100,
                'high' => mt_rand(2800, 5500) / 100,
            };
            $roi30 = match ($risk) {
                'low' => mt_rand(800, 4200) / 100,
                'medium' => mt_rand(1500, 9800) / 100,
                'high' => mt_rand(-1800, 24000) / 100,
            };
            $roi7 = round($roi30 / mt_rand(2, 5) + mt_rand(-800, 1200) / 100, 2);
            $dd = match ($risk) {
                'low' => mt_rand(300, 1200) / 100,
                'medium' => mt_rand(800, 2500) / 100,
                'high' => mt_rand(1500, 6000) / 100,
            };
            $pnl = round(mt_rand(15000, 890000) * ($roi30 >= 0 ? 1 : -1) / 100, 2);
            $trades = match ($risk) {
                'low' => mt_rand(40, 180),
                'medium' => mt_rand(120, 600),
                'high' => mt_rand(400, 2400),
            };

            $tagCount = mt_rand(2, 4);
            $tags = [];
            $pool = self::TAG_POOL;
            shuffle($pool);
            for ($t = 0; $t < $tagCount; $t++) {
                $tags[] = $pool[$t];
            }

            $history = $this->equityCurve($roi30, $risk);

            $trader = CopyTrader::create([
                'user_id' => $owner->id,
                'display_name' => $name,
                'slug' => $slug,
                'tagline' => $tagline,
                'bio' => $tagline . '. Trading Solana markets with a disciplined strategy. ' .
                    ($verified ? 'Verified track record on Veridian.' : 'Public track record on Veridian.'),
                'avatar_url' => 'avatars/trader-' . $i . '.jpg',
                'wallet_address' => $this->fakeWallet($i),
                'tags' => $tags,
                'risk_level' => $risk,
                'is_verified' => $verified,
                'is_featured' => $featured,
                'is_active' => true,
                'followers_count' => mt_rand(12, 4800),
                'trades_count' => $trades,
                'total_pnl_usd' => $pnl,
                'roi_7d' => $roi7,
                'roi_30d' => $roi30,
                'win_rate' => $winRate,
                'max_drawdown' => $dd,
                'volume_24h_usd' => mt_rand(5000, 480000),
                'avg_trade_usd' => mt_rand(120, 8500),
                'stats_history' => $history,
                'socials' => mt_rand(0, 1) ? ['x' => '@' . $name] : null,
                'created_at' => now()->subDays(mt_rand(30, 720)),
            ]);

            $this->seedTrades($trader, $risk, $i);
        }
    }

    private function equityCurve(float $roi30, string $risk): array
    {
        $n = 30;
        $vol = match ($risk) { 'low' => 0.012, 'medium' => 0.025, 'high' => 0.05 };
        $drift = log(max(1 + $roi30 / 100, 0.05)) / ($n - 1);

        $vals = [];
        $v = 100.0;
        for ($d = 0; $d < $n; $d++) {
            $shock = 0.0;
            if ($risk === 'high' && mt_rand(0, 19) === 0) {
                $shock = -(mt_rand(200, 600) / 10000);
            } elseif ($risk === 'medium' && mt_rand(0, 39) === 0) {
                $shock = -(mt_rand(100, 300) / 10000);
            }
            $noise = ($d === 0) ? 0.0 : (mt_rand(-10000, 10000) / 10000) * $vol;
            $v *= exp($drift + $noise + $shock);
            $vals[] = $v;
        }

        $targetEnd = 100.0 * (1 + $roi30 / 100);
        $end = end($vals);
        if ($end > 0.0001) {
            $ratio = $targetEnd / $end;
            foreach ($vals as $idx => $v) {
                $vals[$idx] = $v * pow($ratio, $idx / ($n - 1));
            }
        }

        return array_map(fn ($v) => round($v, 2), $vals);
    }

    private function seedTrades(CopyTrader $trader, string $risk, int $seed): void
    {
        $count = match ($risk) { 'low' => 30, 'medium' => 55, 'high' => 90 };
        $win = $trader->win_rate / 100;
        $baseUsd = max(50, (float) $trader->avg_trade_usd);

        for ($k = 0; $k < $count; $k++) {
            [$sym, $mint, $pLo, $pHi] = self::TOKENS[($seed * 7 + $k * 3) % count(self::TOKENS)];
            $price = round($pLo + mt_rand(0, 10000) / 10000 * ($pHi - $pLo), 8);
            $isBuy = mt_rand(0, 99) < 62;
            $usd = round($baseUsd * (0.35 + mt_rand(0, 1300) / 1000), 2);
            $amount = $usd / max($price, 0.00000001);
            $hoursAgo = mt_rand(1, 720);

            CopyTrade::create([
                'copy_trader_id' => $trader->id,
                'subscription_id' => null,
                'action' => $isBuy ? 'buy' : 'sell',
                'mint' => $mint,
                'symbol' => $sym,
                'amount' => round($amount, 6),
                'price' => $price,
                'total_usd' => $usd,
                'tx_signature' => $this->fakeSignature($seed * 100 + $k),
                'executed_at' => now()->subHours($hoursAgo)->subMinutes(mt_rand(0, 59)),
                'created_at' => now()->subHours($hoursAgo),
            ]);
        }
    }

    private function fakeWallet(int $seed): string
    {
        $alphabet = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
        $state = crc32('copytrader-' . $seed);
        $out = '';
        for ($i = 0; $i < 44; $i++) {
            $state = ($state * 1103515245 + 12345) & 0x7fffffff;
            $out .= $alphabet[$state % strlen($alphabet)];
        }
        return $out;
    }

    private function fakeSignature(int $seed): string
    {
        $alphabet = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
        $state = crc32('copytx-' . $seed);
        $out = '';
        for ($i = 0; $i < 88; $i++) {
            $state = ($state * 1103515245 + 12345) & 0x7fffffff;
            $out .= $alphabet[$state % strlen($alphabet)];
        }
        return $out;
    }
}
