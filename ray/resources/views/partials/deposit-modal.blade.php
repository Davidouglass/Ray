{{-- Deposit modal (global, included from top-nav). Expects $chainArt, $chainNames. --}}
<div class="pf-modal" id="dpModal" aria-hidden="true">
    <div class="pf-modal-overlay" data-dp-close></div>
    <div class="pf-modal-card" role="dialog" aria-modal="true" aria-label="Deposit">
        <div class="pf-modal-head">
            <span class="pf-modal-ic"><i class="ri-download-2-line"></i></span>
            <div>
                <h3>Deposit</h3>
                <span class="pf-modal-sub">Send funds to one of your deposit addresses.</span>
            </div>
            <button type="button" class="pf-modal-x" data-dp-close aria-label="Close"><i class="ri-close-line"></i></button>
        </div>

        <div class="pf-modal-body">
            <div class="dp-chains" id="dpChains">
                @foreach ($chainArt as $ck => $art)
                    <button type="button" class="dp-chain{{ $ck === 'sol' ? ' is-active' : '' }}" data-dp-chain="{{ $ck }}">
                        <img src="{{ $art }}" alt=""><span>{{ $chainNames[$ck] ?? ucfirst($ck) }}</span>
                    </button>
                @endforeach
            </div>

            <div class="dp-list" id="dpList"></div>

            <div class="dp-empty" id="dpEmpty" hidden>
                <i class="ri-hand-coin-line"></i>
                <p>No deposit address on <b id="dpEmptyChain">this network</b> yet — add one below.</p>
            </div>

            <div class="pf-divider">add a deposit address</div>
            <form class="pf-manual-form" id="dpForm">
                <input class="fx-input" id="dpAddress" name="address" placeholder="Wallet address" required autocomplete="off" spellcheck="false">
                <input class="fx-input" id="dpLabel" name="label" placeholder="Label (optional)" autocomplete="off">
                <button class="fx-btn" type="submit"><i class="ri-add-line"></i> Add deposit address</button>
            </form>
            <div class="dp-msg" id="dpMsg"></div>
        </div>

        <div class="pf-modal-foot">
            Only send assets that match the selected network. Funds sent on the wrong network cannot be recovered.
        </div>
    </div>
</div>
