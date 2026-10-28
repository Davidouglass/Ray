{{-- Standard in-app order panel. Wired by public/js/axiom-trade.js.
     Rendered server-side on the token page and rebuilt as-is by the
     listing-page buy modal, so keep the markup in sync with that script. --}}
<div class="fx-panel axiom-trade" data-mint="{{ $mint }}" data-symbol="{{ $symbol }}" data-price="{{ $price ?? 0 }}">
    <div class="at-head">
        <div class="fx-tabs" style="flex:1">
            <button type="button" class="fx-tab active" data-side="buy">Buy</button>
            <button type="button" class="fx-tab" data-side="sell">Sell</button>
        </div>
        <button type="button" class="at-gear" data-at-gear title="Slippage settings" aria-label="Slippage settings"><i class="ri-equalizer-2-line"></i></button>
    </div>

    <div class="at-slip-panel" data-at-slip-panel hidden>
        <div class="at-slip-head">Slippage tolerance</div>
        <div class="at-slip-row">
            <button type="button" class="at-slip-opt" data-bps="50">0.5%</button>
            <button type="button" class="at-slip-opt is-active" data-bps="100">1%</button>
            <button type="button" class="at-slip-opt" data-bps="300">3%</button>
            <button type="button" class="at-slip-opt" data-bps="500">5%</button>
        </div>
        <label class="at-slip-custom">
            <span>Custom</span>
            <input type="text" inputmode="decimal" data-at-slip-custom placeholder="0.5">
            <span>%</span>
        </label>
    </div>

    <div class="at-box">
        <div class="at-box-top"><span>You pay</span><span class="at-bal" data-at-paybal></span></div>
        <div class="at-box-mid">
            <input type="text" class="at-input" data-at-amount inputmode="decimal" placeholder="0.0" autocomplete="off" aria-label="Amount to pay">
            <select class="at-select" data-at-paysel aria-label="Pay token"></select>
        </div>
        <div class="at-box-bot">
            <span class="at-usd" data-at-payusd></span>
            <div class="at-presets">
                <button type="button" data-pct="25">25%</button>
                <button type="button" data-pct="50">50%</button>
                <button type="button" data-pct="75">75%</button>
                <button type="button" data-pct="100">MAX</button>
            </div>
        </div>
    </div>

    <div class="at-box">
        <div class="at-box-top"><span>You receive</span><span class="at-bal" data-at-getbal></span></div>
        <div class="at-box-mid">
            <div class="at-out" data-at-out>0</div>
            <select class="at-select" data-at-getsel aria-label="Receive token"></select>
        </div>
        <div class="at-box-bot"><span class="at-usd" data-at-getusd></span></div>
    </div>

    <div class="at-info" data-at-info></div>

    <button type="button" class="fx-btn" data-at-btn>Connect wallet</button>
    <div class="at-status" data-at-status></div>
    <div class="at-note"><i class="ri-lock-line"></i> Trades are signed in your wallet — no redirects, keys never leave your device.</div>
</div>
