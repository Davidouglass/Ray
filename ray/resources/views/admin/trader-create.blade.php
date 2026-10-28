@extends('admin.layout')

@section('content')
    <div class="adm-page-head">
        <div>
            <div class="adm-title">New trader</div>
            <div class="adm-subtitle">Create a copy-trading profile</div>
        </div>
        <span class="adm-header-spacer"></span>
        <a href="/admin/traders" class="adm-btn"><i class="ri-arrow-left-line"></i> Back</a>
    </div>

    <div class="adm-card">
        <form method="POST" action="/admin/traders" class="adm-card-body" enctype="multipart/form-data">
            @csrf

            <div class="adm-form">
                <div class="adm-field">
                    <label>Display name</label>
                    <input type="text" name="display_name" value="{{ old('display_name') }}" required>
                </div>
                <div class="adm-field">
                    <label>Wallet address</label>
                    <input type="text" name="wallet_address" value="{{ old('wallet_address') }}">
                </div>

                <div class="adm-field full">
                    <label>Tagline</label>
                    <input type="text" name="tagline" value="{{ old('tagline') }}">
                </div>

                <div class="adm-field">
                    <label>Avatar image</label>
                    <input type="file" name="avatar" accept="image/*">
                </div>
                <div class="adm-field">
                    <label>…or avatar URL</label>
                    <input type="text" name="avatar_url" value="{{ old('avatar_url') }}">
                </div>

                <div class="adm-field full">
                    <label>Bio</label>
                    <textarea name="bio">{{ old('bio') }}</textarea>
                </div>

                <div class="adm-field">
                    <label>Risk level</label>
                    <select name="risk_level">
                        @foreach (['low', 'medium', 'high'] as $r)
                            <option value="{{ $r }}" {{ old('risk_level') === $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="adm-field">
                    <label>Tags (comma separated)</label>
                    <input type="text" name="tags" value="{{ old('tags') }}">
                </div>

                <div class="adm-field">
                    <label>Total PnL (USD)</label>
                    <input type="number" step="any" name="total_pnl_usd" value="{{ old('total_pnl_usd', 0) }}">
                </div>
                <div class="adm-field">
                    <label>Volume 24h (USD)</label>
                    <input type="number" step="any" name="volume_24h_usd" value="{{ old('volume_24h_usd', 0) }}">
                </div>

                <div class="adm-field">
                    <label>ROI 7d (%)</label>
                    <input type="number" step="any" name="roi_7d" value="{{ old('roi_7d', 0) }}">
                </div>
                <div class="adm-field">
                    <label>ROI 30d (%)</label>
                    <input type="number" step="any" name="roi_30d" value="{{ old('roi_30d', 0) }}">
                </div>

                <div class="adm-field">
                    <label>Win rate (%)</label>
                    <input type="number" step="any" min="0" max="100" name="win_rate" value="{{ old('win_rate', 0) }}">
                </div>
                <div class="adm-field">
                    <label>Max drawdown (%)</label>
                    <input type="number" step="any" name="max_drawdown" value="{{ old('max_drawdown', 0) }}">
                </div>

                <div class="adm-field">
                    <label>Followers</label>
                    <input type="number" min="0" name="followers_count" value="{{ old('followers_count', 0) }}">
                </div>
                <div class="adm-field">
                    <label>Trades</label>
                    <input type="number" min="0" name="trades_count" value="{{ old('trades_count', 0) }}">
                </div>

                <div class="adm-field">
                    <label>Avg trade (USD)</label>
                    <input type="number" step="any" name="avg_trade_usd" value="{{ old('avg_trade_usd', 0) }}">
                </div>
                <div class="adm-field full">
                    <label>Visibility</label>
                    <div class="adm-checks">
                        <label class="adm-check"><input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}> Active</label>
                        <label class="adm-check"><input type="checkbox" name="is_verified" value="1" {{ old('is_verified') ? 'checked' : '' }}> Verified</label>
                        <label class="adm-check"><input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}> Featured</label>
                    </div>
                </div>

                <div class="adm-form-actions">
                    <button type="submit" class="adm-btn primary"><i class="ri-add-line"></i> Create trader</button>
                    <a href="/admin/traders" class="adm-btn">Cancel</a>
                </div>
            </div>
        </form>
    </div>
@endsection
