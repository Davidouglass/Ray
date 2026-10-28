@extends('admin.layout')

@section('content')
    <div class="adm-page-head">
        <div>
            <div class="adm-title">Edit trader</div>
            <div class="adm-subtitle">{{ $trader->display_name }} · <span class="adm-mono">{{ $trader->slug }}</span></div>
        </div>
        <span class="adm-header-spacer"></span>
        <a href="/admin/traders" class="adm-btn"><i class="ri-arrow-left-line"></i> Back</a>
    </div>

    @php
        $tagString = old('tags', is_array($trader->tags) ? implode(', ', $trader->tags) : (string) $trader->tags);
    @endphp

    <div class="adm-card">
        <form method="POST" action="/admin/traders/{{ $trader->slug }}" class="adm-card-body" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="adm-form">
                <div class="adm-field">
                    <label>Display name</label>
                    <input type="text" name="display_name" value="{{ old('display_name', $trader->display_name) }}" required>
                </div>
                <div class="adm-field">
                    <label>Wallet address</label>
                    <input type="text" name="wallet_address" value="{{ old('wallet_address', $trader->wallet_address) }}">
                </div>

                <div class="adm-field full">
                    <label>Tagline</label>
                    <input type="text" name="tagline" value="{{ old('tagline', $trader->tagline) }}">
                </div>

                <div class="adm-field">
                    <label>Avatar image</label>
                    <input type="file" name="avatar" accept="image/*">
                    @if ($trader->avatar_url)
                        <span class="adm-hint">Current: <img src="{{ $trader->avatar_url }}" alt="" class="adm-avatar-mini"></span>
                    @endif
                </div>
                <div class="adm-field">
                    <label>…or avatar URL</label>
                    <input type="text" name="avatar_url" value="{{ old('avatar_url', $trader->avatar_url) }}">
                </div>

                <div class="adm-field full">
                    <label>Bio</label>
                    <textarea name="bio">{{ old('bio', $trader->bio) }}</textarea>
                </div>

                <div class="adm-field">
                    <label>Risk level</label>
                    <select name="risk_level">
                        @foreach (['low', 'medium', 'high'] as $r)
                            <option value="{{ $r }}" {{ old('risk_level', $trader->risk_level) === $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="adm-field">
                    <label>Tags (comma separated)</label>
                    <input type="text" name="tags" value="{{ $tagString }}">
                </div>

                <div class="adm-field">
                    <label>Total PnL (USD)</label>
                    <input type="number" step="any" name="total_pnl_usd" value="{{ old('total_pnl_usd', $trader->total_pnl_usd) }}">
                </div>
                <div class="adm-field">
                    <label>Volume 24h (USD)</label>
                    <input type="number" step="any" name="volume_24h_usd" value="{{ old('volume_24h_usd', $trader->volume_24h_usd) }}">
                </div>

                <div class="adm-field">
                    <label>ROI 7d (%)</label>
                    <input type="number" step="any" name="roi_7d" value="{{ old('roi_7d', $trader->roi_7d) }}">
                </div>
                <div class="adm-field">
                    <label>ROI 30d (%)</label>
                    <input type="number" step="any" name="roi_30d" value="{{ old('roi_30d', $trader->roi_30d) }}">
                </div>

                <div class="adm-field">
                    <label>Win rate (%)</label>
                    <input type="number" step="any" min="0" max="100" name="win_rate" value="{{ old('win_rate', $trader->win_rate) }}">
                </div>
                <div class="adm-field">
                    <label>Max drawdown (%)</label>
                    <input type="number" step="any" name="max_drawdown" value="{{ old('max_drawdown', $trader->max_drawdown) }}">
                </div>

                <div class="adm-field">
                    <label>Followers</label>
                    <input type="number" min="0" name="followers_count" value="{{ old('followers_count', $trader->followers_count) }}">
                </div>
                <div class="adm-field">
                    <label>Trades</label>
                    <input type="number" min="0" name="trades_count" value="{{ old('trades_count', $trader->trades_count) }}">
                </div>

                <div class="adm-field">
                    <label>Avg trade (USD)</label>
                    <input type="number" step="any" name="avg_trade_usd" value="{{ old('avg_trade_usd', $trader->avg_trade_usd) }}">
                </div>
                <div class="adm-field full">
                    <label>Visibility</label>
                    <div class="adm-checks">
                        <label class="adm-check"><input type="checkbox" name="is_active" value="1" {{ old('is_active', $trader->is_active) ? 'checked' : '' }}> Active</label>
                        <label class="adm-check"><input type="checkbox" name="is_verified" value="1" {{ old('is_verified', $trader->is_verified) ? 'checked' : '' }}> Verified</label>
                        <label class="adm-check"><input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $trader->is_featured) ? 'checked' : '' }}> Featured</label>
                    </div>
                </div>

                <div class="adm-form-actions">
                    <button type="submit" class="adm-btn primary"><i class="ri-save-line"></i> Save changes</button>
                    <a href="/admin/traders" class="adm-btn">Cancel</a>
                </div>
            </div>
        </form>
    </div>

    <div class="adm-card">
        <div class="adm-card-body">
            <div class="adm-section-title danger">Danger zone</div>
            <form method="POST" action="/admin/traders/{{ $trader->slug }}" onsubmit="return confirm('Delete this trader permanently?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="adm-btn danger"><i class="ri-delete-bin-line"></i> Delete trader</button>
            </form>
        </div>
    </div>
@endsection
