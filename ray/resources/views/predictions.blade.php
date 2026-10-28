@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/predictions.css') }}?v={{ @filemtime(public_path('css/predictions.css')) }}">
@endpush

@section('content')
<div class="predict" id="predictRoot"
     data-gamma="https://gamma-api.polymarket.com"
     data-data="https://data-api.polymarket.com"
     data-clob="https://clob.polymarket.com">

    <div class="browser-bar">
        <span class="back">&#8249;</span>
        <div class="searchbar"><span>&#8981;</span>Search markets...</div>
        <span class="browser-star">&#9734;</span>
        <span class="browser-divider"></span>
    </div>

    <header class="market-head">
        <div class="bitcoin-icon">&#8383;</div>
        <div style="min-width:0">
            <div class="market-title-line">
                <span class="market-title" id="pcTitle">Loading market&hellip;</span>
                <span class="market-tools">&#9827; &#9734; &#8613;</span>
            </div>
            <div class="market-sub">
                <span class="closed-dot" id="pcStateDot"></span>
                <span id="pcStateText">Live</span>
                <span class="closed-dot"></span>
                <span id="pcSubMkt">Bitcoin Up or Down</span>
                <b id="pcSubChance">--%</b>
                <span>&#8599;</span>
                <span>&#120143;</span>
            </div>
        </div>
        <div class="head-spacer"></div>
        <div class="market-stat"><span>Price to Beat</span><b id="pcBeat">$--</b></div>
        <div class="market-stat"><span>Volume</span><b id="pcVol">$--</b></div>
        <div class="market-stat"><span>Liquidity</span><b id="pcLiq">$--</b></div>
        <span class="help">&#9432;</span>
        <span class="hide-resolved"><span class="small-toggle" id="pcHideResolved"></span> Hide Resolved</span>
        <button class="tiny-control">&#9776;</button>
    </header>

    <nav class="main-tabs" id="pcTabs">
        <button class="active" data-tab="overview">Overview</button>
        <button data-tab="price">BTC Price</button>
        <button data-tab="compare">Compare</button>
        <button data-tab="market">Market</button>
    </nav>

    <main class="body-layout">
        <section class="market-left">
            <div class="tab-pane active" data-pane="overview">
                <div class="column-labels"><span>Outcome</span><span>% Chance</span></div>
                <div class="outcome-row">
                    <button class="expand">&#8964;</button>
                    <div class="outcome-coin">&#8383;</div>
                    <div class="outcome-info">
                        <div class="outcome-title" id="pcOutcomeTitle">Bitcoin Up or Down</div>
                        <div class="outcome-sub"><span id="pcOutVol">&#9638; $--</span><span id="pcOutLiq">&#9672; $--</span></div>
                    </div>
                    <span class="event-state" id="pcEventState">LIVE</span>
                    <span class="outcome-links">&#8599;&#12288;&#120143;</span>
                    <div class="chance"><span id="pcChance">--%</span> <small id="pcChanceDelta">&#8963; 0%</small></div>
                    <span class="up-status" id="pcStatus">&#9814; Up</span>
                </div>

                <div class="chart-and-book">
                    <section class="chart-side">
                        <div class="periods" id="pcPeriods">
                            <button data-gran="60">1m</button>
                            <button data-gran="300">5m</button>
                            <button data-gran="3600">1H</button>
                            <button data-gran="21600">6H</button>
                            <button data-gran="86400">1D</button>
                            <button data-gran="604800">1W</button>
                            <button data-gran="2592000">1M</button>
                            <button class="active" data-gran="300">ALL</button>
                        </div>
                        <div class="chart-plot" id="pcPlot">
                            <div class="pc-tv-chart" id="pcTvChart"></div>
                            <div class="pc-chart-status" id="pcChartStatus">Loading chart&hellip;</div>
                        </div>
                        <div class="bottom-time">
                            <button class="live-market" id="pcGoLive"><i></i>Go to live market&#12288;&#8250;</button>
                            <button class="active">Past&#12288;&#8964;</button>
                            <button>&#8595;</button><button>&#8595;</button><button>&#8593;</button>
                            <span class="time-spacer"></span>
                            <button class="time-pill" id="pcPill0">--</button>
                            <button class="time-pill" id="pcPill1">--</button>
                            <button class="time-pill" id="pcPill2">--</button>
                            <button class="time-pill" id="pcPill3">--</button>
                            <button class="time-more">More &#8964;</button>
                        </div>
                    </section>
                    <section class="book-side">
                        <div class="book-title">Order Book</div>
                        <div class="book-columns"><span>Price</span><span>Shares</span><span>Total USD</span></div>
                        <div class="book-space" id="pcBook"></div>
                        <div class="spread" id="pcSpread">Spread:&#12288;&mdash;</div>
                    </section>
                </div>
            </div>

            <div class="tab-pane" data-pane="price" hidden>
                <div class="pane-head">
                    <div class="pane-title">Bitcoin Price</div>
                    <div class="pane-sub" id="pcPriceSub">Live BTC / USD &middot; Coinbase</div>
                </div>
                <div class="price-stats">
                    <div class="pstat"><span>Last</span><b id="pcPsLast">--</b></div>
                    <div class="pstat"><span>24h Change</span><b id="pcPsChg">--</b></div>
                    <div class="pstat"><span>24h High</span><b id="pcPsHigh">--</b></div>
                    <div class="pstat"><span>24h Low</span><b id="pcPsLow">--</b></div>
                    <div class="pstat"><span>Price to Beat</span><b id="pcPsBeat">--</b></div>
                </div>
                <div class="chart-plot" id="pcPricePlot">
                    <div class="pc-tv-chart" id="pcPriceTvChart"></div>
                    <div class="pc-chart-status" id="pcPriceStatus">Loading chart&hellip;</div>
                </div>
            </div>

            <div class="tab-pane" data-pane="compare" hidden>
                <div class="pane-head">
                    <div class="pane-title">Compare Markets</div>
                    <div class="pane-sub" id="pcCompareSub">Bitcoin Up or Down &middot; live windows</div>
                </div>
                <div class="compare-table">
                    <div class="compare-head">
                        <span>Market</span><span>Up</span><span>Down</span><span>Volume</span><span>Liquidity</span><span>Ends</span><span></span>
                    </div>
                    <div class="compare-scroll" id="pcCompareRows"></div>
                </div>
            </div>

            <div class="tab-pane" data-pane="market" hidden>
                <div class="pane-head">
                    <div class="pane-title" id="pcMarketTitle">Market</div>
                    <div class="pane-sub" id="pcMarketSub">Bitcoin / USD &middot; TradingView</div>
                </div>
                <div class="market-scroll">
                    <div class="market-grid" id="pcMarketBody"></div>
                    <div class="market-section-title">Bitcoin price &middot; TradingView</div>
                    <div class="chart-plot market-chart" id="pcMarketPlot">
                        <div class="pc-tv-chart" id="pcMarketTvChart"></div>
                        <div class="pc-chart-status" id="pcMarketStatus">Loading chart&hellip;</div>
                    </div>
                    <div class="market-section-title">Recent activity</div>
                    <div class="market-trades" id="pcMarketTrades"></div>
                </div>
            </div>
        </section>

        <aside class="order-panel">
            <div class="buy-sell" id="pcSide">
                <button class="buy active" data-action="Buy">Buy</button>
                <button data-action="Sell">Sell</button>
            </div>
            <div class="order-types" id="pcOtype">
                <button class="active" data-otype="Market">Market</button>
                <button data-otype="Limit">Limit</button>
                <span class="currency-select">&#9673; SOL USDC 0.00 &#8964;</span>
            </div>
            <div class="limit-row" id="pcLimitRow" hidden>
                <div class="limit-top"><span>LIMIT PRICE</span><span id="pcLimitQuote">&mdash;</span></div>
                <div class="limit-stepper">
                    <button type="button" id="pcLimitMinus">&minus;</button>
                    <input id="pcLimitPrice" type="number" step="0.1" min="0.1" max="99.9" inputmode="decimal" value="50.0">
                    <span class="limit-unit">&cent;</span>
                    <button type="button" id="pcLimitPlus">+</button>
                </div>
                <div class="limit-quick" id="pcLimitQuick">
                    <button type="button" data-lp="bid">Bid</button>
                    <button type="button" data-lp="mid">Mid</button>
                    <button type="button" data-lp="ask">Ask</button>
                </div>
            </div>
            <div class="event-row">
                <span class="event-icon">&#8383;</span>
                <span class="event-name" id="pcEventName">Bitcoin Up or Down</span>
            </div>
            <div class="outcome-toggle" id="pcOutcomes">
                <button class="active" data-outcome="0">Up<b id="pcUpPrice">--&#162;</b></button>
                <button data-outcome="1">Down<b id="pcDownPrice">--</b></button>
            </div>
            <div class="amount-section">
                <div class="amount-top"><span>AMOUNT</span><span id="amountDisplay">0.0</span><span class="currency-symbol">$</span></div>
                <div class="amount-value"><span></span><span></span></div>
            </div>
            <div class="presets" id="pcPresets">
                <button data-amount="1">$1<small>$1.00</small></button>
                <button data-amount="10">$10<small>$10.00</small></button>
                <button data-amount="25">$25<small>$25.00</small></button>
                <button data-amount="100">$100<small>$100.00</small></button>
                <button aria-label="Edit amount" id="pcEditAmount">&#9827;</button>
            </div>
            <button class="closed-button" id="pcSubmit" disabled>Market Closed</button>
            <div class="performance">
                <div class="metric"><span>Bought</span><b class="pos" id="pcBought">$0.00</b></div>
                <div class="metric"><span>Sold</span><b class="neg" id="pcSold">$0.00</b></div>
                <div class="metric"><span>Holding</span><b id="pcHolding">$0.00</b></div>
                <div class="metric"><span>PnL</span><b class="pos" id="pcPnl">+$0.00 (+0%)</b></div>
            </div>
        </aside>
    </main>
</div>
@endsection

@push('scripts')
<script type="text/javascript" src="https://s3.tradingview.com/tv.js"></script>
<script src="{{ asset('js/axiom-predict.js') }}?v={{ @filemtime(public_path('js/axiom-predict.js')) }}" defer></script>
@endpush
