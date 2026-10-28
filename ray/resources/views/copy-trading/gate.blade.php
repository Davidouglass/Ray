@extends('layouts.app')

@section('content')
<div class="fx-page">
    <div class="cp-hero">
        <div class="cp-hero-glow"></div>
        <div class="cp-hero-inner">
            <div class="cp-hero-copy">
                <div class="cp-eyebrow"><span class="cp-live-dot"></span>{{ number_format($stats['traders']) }} verified traders · live execution</div>
                <h1>Copy the best traders<br>on Solana <span class="cp-gradient-text">automatically</span></h1>
                <p>Subscribe once. Every position they open is mirrored to your wallet in real time — sized to your balance, exited when they exit.</p>
            </div>
            <div class="cp-hero-stats">
                <div class="cp-hstat"><b>{{ number_format($stats['traders']) }}</b><span>Elite traders</span></div>
                <div class="cp-hstat"><b>{{ number_format($stats['followers']) }}</b><span>Active copiers</span></div>
                <div class="cp-hstat"><b>${{ rtrim(rtrim(number_format($stats['volume'], 0), '0'), ',') }}</b><span>24h copied volume</span></div>
                <div class="cp-hstat"><b>{{ number_format($stats['winRate'], 1) }}%</b><span>Avg win rate</span></div>
            </div>
        </div>
    </div>

    <div class="cp-gate">
        <div class="cp-gate-lock"><i class="ri-lock-2-line"></i></div>
        <h2>Unlock copy trading</h2>
        <p class="cp-gate-sub">Traders are hidden until your funding is set up. Connect a wallet and add a deposit address to browse the marketplace and start copying.</p>

        <ul class="cp-gate-steps">
            <li class="{{ $hasWallet ? 'done' : '' }}">
                <span class="cp-gate-ic"><i class="{{ $hasWallet ? 'ri-checkbox-circle-fill' : 'ri-wallet-3-line' }}"></i></span>
                <div class="cp-gate-step-txt">
                    <b>Connect a wallet</b>
                    <span>Link the Solana wallet copy trades will execute from.</span>
                </div>
                @if ($hasWallet)
                    <span class="cp-gate-done">Done</span>
                @else
                    <button type="button" class="fx-btn cp-hero-btn" data-wallet-connect><i class="ri-wallet-3-line"></i>Connect Wallet</button>
                @endif
            </li>
            <li class="{{ $hasDeposit ? 'done' : '' }}">
                <span class="cp-gate-ic"><i class="{{ $hasDeposit ? 'ri-checkbox-circle-fill' : 'ri-download-2-line' }}"></i></span>
                <div class="cp-gate-step-txt">
                    <b>Add a deposit address</b>
                    <span>Set the wallet you'll fund your copy trades from.</span>
                </div>
                @if ($hasDeposit)
                    <span class="cp-gate-done">Done</span>
                @else
                    <button type="button" class="fx-btn ghost" data-wallet-deposit><i class="ri-download-2-line"></i>Deposit</button>
                @endif
            </li>
        </ul>

        <div class="cp-gate-note"><i class="ri-information-line"></i> $100/month is charged only when you subscribe to a trader.</div>
    </div>
</div>

<style>
.cp-hero { position:relative; border:1px solid var(--primary-stroke); border-radius:18px; overflow:hidden; background:var(--bg-secondary); }
.cp-hero-glow { position:absolute; inset:0; background:
    radial-gradient(700px 200px at 15% -30%, rgba(59,130,246,.28), transparent 65%),
    radial-gradient(600px 220px at 85% 120%, rgba(47,227,172,.16), transparent 65%); }
.cp-hero-inner { position:relative; display:flex; gap:28px; padding:34px 30px; flex-wrap:wrap; align-items:center; }
.cp-hero-copy { flex:1 1 380px; min-width:280px; }
.cp-eyebrow { display:inline-flex; align-items:center; gap:8px; font-size:11.5px; font-weight:650; text-transform:uppercase; letter-spacing:.07em; color:var(--blue); background:rgba(59,130,246,.1); border:1px solid rgba(59,130,246,.3); padding:5px 11px; border-radius:999px; }
.cp-live-dot { width:7px; height:7px; border-radius:50%; background:var(--up); box-shadow:0 0 6px var(--up); animation:cpulse 1.6s infinite; }
@keyframes cpulse { 0%,100%{opacity:1} 50%{opacity:.35} }
.cp-hero-copy h1 { font-size:34px; line-height:1.12; font-weight:800; color:var(--text-primary); margin:16px 0 12px; letter-spacing:-.02em; }
.cp-gradient-text { background:linear-gradient(90deg,#5AA9FF,#2FE3AC); -webkit-background-clip:text; background-clip:text; color:transparent; }
.cp-hero-copy p { font-size:14.5px; color:var(--text-secondary); line-height:1.6; max-width:520px; }
.cp-hero-stats { flex:0 1 340px; display:grid; grid-template-columns:1fr 1fr; gap:12px; }
.cp-hstat { background:rgba(255,255,255,.035); border:1px solid var(--primary-stroke); border-radius:12px; padding:16px 16px; display:flex; flex-direction:column; gap:4px; }
.cp-hstat b { font-size:22px; font-weight:800; color:var(--text-primary); letter-spacing:-.01em; }
.cp-hstat span { font-size:10px; text-transform:uppercase; letter-spacing:.06em; color:var(--text-tertiary); }
.cp-gate { border:1px solid var(--primary-stroke); border-radius:18px; background:var(--bg-secondary); padding:34px 30px; text-align:center; max-width:720px; margin:0 auto; }
.cp-gate-lock { width:54px; height:54px; border-radius:15px; margin:0 auto 16px; display:flex; align-items:center; justify-content:center; font-size:24px; color:var(--blue); background:rgba(59,130,246,.12); border:1px solid rgba(59,130,246,.3); }
.cp-gate h2 { font-size:22px; font-weight:800; color:var(--text-primary); margin:0 0 8px; }
.cp-gate-sub { font-size:13.5px; color:var(--text-secondary); line-height:1.6; max-width:520px; margin:0 auto 22px; }
.cp-gate-steps { list-style:none; padding:0; margin:0 auto 20px; display:flex; flex-direction:column; gap:10px; max-width:560px; text-align:left; }
.cp-gate-steps li { display:flex; align-items:center; gap:13px; padding:14px 16px; border:1px solid var(--primary-stroke); border-radius:12px; background:rgba(255,255,255,.02); }
.cp-gate-steps li.done { border-color:rgba(47,227,172,.35); background:rgba(47,227,172,.05); }
.cp-gate-ic { flex:none; width:38px; height:38px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:18px; color:var(--text-tertiary); background:var(--bg-tertiary); }
.cp-gate-steps li.done .cp-gate-ic { color:var(--up); background:rgba(47,227,172,.12); }
.cp-gate-step-txt { flex:1; min-width:0; display:flex; flex-direction:column; gap:2px; }
.cp-gate-step-txt b { font-size:13.5px; color:var(--text-primary); }
.cp-gate-step-txt span { font-size:12px; color:var(--text-tertiary); }
.cp-gate-done { flex:none; font-size:11.5px; font-weight:700; color:var(--up); }
.cp-gate-steps .fx-btn { flex:none; }
.cp-gate-note { font-size:12px; color:var(--text-tertiary); display:inline-flex; align-items:center; gap:6px; }
@media (max-width:640px){
    .cp-hero-inner{padding:24px 18px}
    .cp-hero-copy h1{font-size:26px}
    .cp-hero-stats{grid-template-columns:1fr 1fr;flex:1 1 100%}
    .cp-gate{padding:24px 18px}
    .cp-gate-steps li{flex-wrap:wrap}
    .cp-gate-steps .fx-btn{width:100%;justify-content:center}
}
</style>
@endsection
