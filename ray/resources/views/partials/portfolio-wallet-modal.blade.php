<div class="pf-modal" id="pfWallet" aria-hidden="true">
    <div class="pf-modal-overlay" data-pf-close></div>
    <div class="pf-modal-card" role="dialog" aria-modal="true" aria-label="Connect a wallet">

        <div class="pf-modal-head">
            <span class="pf-modal-ic"><i class="ri-wallet-3-line"></i></span>
            <div>
                <h3>Connect a wallet</h3>
                <span class="pf-modal-sub">Choose how you want to connect to Veridian.</span>
            </div>
            <button type="button" class="pf-modal-x" data-pf-close aria-label="Close"><i class="ri-close-line"></i></button>
        </div>

        <div class="pf-modal-body">
            <button type="button" class="pf-wallet-opt" data-wallet="phantom" data-install="https://phantom.app/">
                <span class="pf-wallet-ico is-logo"><img src="{{ asset('images/wallets/phantom.svg') }}" alt="Phantom" loading="lazy"></span>
                <span class="pf-wallet-name">Phantom</span>
                <span class="pf-wallet-tag off">Install</span>
            </button>
            <button type="button" class="pf-wallet-opt" data-wallet="solflare" data-install="https://solflare.com/">
                <span class="pf-wallet-ico is-logo"><img src="{{ asset('images/wallets/solflare.svg') }}" alt="Solflare" loading="lazy"></span>
                <span class="pf-wallet-name">Solflare</span>
                <span class="pf-wallet-tag off">Install</span>
            </button>
            <button type="button" class="pf-wallet-opt" data-wallet="backpack" data-install="https://backpack.app/">
                <span class="pf-wallet-ico is-logo"><img src="{{ asset('images/wallets/backpack.png') }}" alt="Backpack" loading="lazy"></span>
                <span class="pf-wallet-name">Backpack</span>
                <span class="pf-wallet-tag off">Install</span>
            </button>
            <button type="button" class="pf-wallet-opt" data-wallet="glow" data-install="https://glow.app/">
                <span class="pf-wallet-ico is-logo"><img src="{{ asset('images/wallets/glow.png') }}" alt="Glow" loading="lazy"></span>
                <span class="pf-wallet-name">Glow</span>
                <span class="pf-wallet-tag off">Install</span>
            </button>
            <button type="button" class="pf-wallet-opt" data-wallet="coinbase" data-install="https://www.coinbase.com/wallet">
                <span class="pf-wallet-ico is-logo"><img src="{{ asset('images/wallets/coinbase.png') }}" alt="Coinbase Wallet" loading="lazy"></span>
                <span class="pf-wallet-name">Coinbase Wallet</span>
                <span class="pf-wallet-tag off">Install</span>
            </button>
            <button type="button" class="pf-wallet-opt" data-wallet="okx" data-install="https://www.okx.com/web3">
                <span class="pf-wallet-ico is-logo"><img src="{{ asset('images/wallets/okx.png') }}" alt="OKX Wallet" loading="lazy"></span>
                <span class="pf-wallet-name">OKX Wallet</span>
                <span class="pf-wallet-tag off">Install</span>
            </button>
            <button type="button" class="pf-wallet-opt" data-wallet="trust" data-install="https://trustwallet.com/">
                <span class="pf-wallet-ico is-logo"><img src="{{ asset('images/wallets/trust.svg') }}" alt="Trust Wallet" loading="lazy"></span>
                <span class="pf-wallet-name">Trust Wallet</span>
                <span class="pf-wallet-tag off">Install</span>
            </button>
            <button type="button" class="pf-wallet-opt" data-wallet="generic" data-install="https://phantom.app/">
                <span class="pf-wallet-ico"><i class="ri-global-line"></i></span>
                <span class="pf-wallet-name">Browser wallet</span>
                <span class="pf-wallet-tag off">Detect</span>
            </button>
        </div>

        <div class="pf-modal-body pf-modal-manual">
            <div class="pf-divider">or connect with an address</div>
            <form method="POST" action="/portfolio/connect" class="pf-manual-form">
                @csrf
                <input class="fx-input" name="address" placeholder="Solana wallet address" required autocomplete="off" spellcheck="false">
                <input class="fx-input" name="label" placeholder="Label (optional)" autocomplete="off">
                <button class="fx-btn" type="submit"><i class="ri-link"></i> Connect wallet</button>
            </form>
        </div>

        <div class="pf-modal-foot">
            By connecting you agree to Veridian's <a href="/terms">Terms of Service</a> and <a href="/privacy">Privacy Policy</a>.
        </div>
    </div>
</div>
