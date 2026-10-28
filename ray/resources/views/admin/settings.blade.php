@extends('admin.layout')

@section('content')
    <div class="adm-page-head">
        <div>
            <div class="adm-title">Site settings</div>
            <div class="adm-subtitle">Links, platform access and feature availability.</div>
        </div>
    </div>

    <form method="POST" action="/admin/settings">
        @csrf

        <div class="adm-card">
            <div class="adm-card-head"><i class="ri-customer-service-2-line"></i> Support team</div>
            <div class="adm-card-body">
                <div class="adm-form">
                    <div class="adm-field full">
                        <label>Support team URL</label>
                        <input type="url" name="support_url" value="{{ old('support_url', $supportUrl) }}" placeholder="https://t.me/YourSupportHandle" required>
                        <span class="adm-trader-meta" style="margin-top:2px">Shown in the landing footer, the in-app status bar, the account menu and the Settings → Support tab.</span>
                    </div>
                    <div class="adm-field full">
                        <label class="adm-check"><input type="checkbox" name="support_enabled" value="1" {{ old('support_enabled', $supportEnabled) ? 'checked' : '' }}> Show the support link across the site</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="adm-card">
            <div class="adm-card-head"><i class="ri-share-line"></i> Social links</div>
            <div class="adm-card-body">
                <div class="adm-form">
                    <div class="adm-field">
                        <label>X (Twitter)</label>
                        <input type="url" name="social_x" value="{{ old('social_x', $socials['x']) }}">
                    </div>
                    <div class="adm-field">
                        <label>Discord</label>
                        <input type="url" name="social_discord" value="{{ old('social_discord', $socials['discord']) }}">
                    </div>
                    <div class="adm-field full">
                        <label>Docs</label>
                        <input type="url" name="social_docs" value="{{ old('social_docs', $socials['docs']) }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="adm-card">
            <div class="adm-card-head"><i class="ri-shield-check-line"></i> Platform access</div>
            <div class="adm-card-body">
                <div class="adm-form">
                    <div class="adm-field full">
                        <label class="adm-check"><input type="checkbox" name="maintenance_mode" value="1" {{ old('maintenance_mode', $maintenanceMode) ? 'checked' : '' }}> Maintenance mode (only admins can access the app)</label>
                    </div>
                    <div class="adm-field full">
                        <label>Maintenance message</label>
                        <input type="text" name="maintenance_message" value="{{ old('maintenance_message', $maintenanceMessage) }}" maxlength="400">
                    </div>
                    <div class="adm-field">
                        <label>Maintenance starts (optional)</label>
                        <input type="datetime-local" name="maintenance_starts_at" value="{{ old('maintenance_starts_at', $maintenanceStarts) }}">
                    </div>
                    <div class="adm-field">
                        <label>Maintenance ends (optional)</label>
                        <input type="datetime-local" name="maintenance_ends_at" value="{{ old('maintenance_ends_at', $maintenanceEnds) }}">
                        @error('maintenance_ends_at')
                            <span class="adm-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="adm-field full">
                        <span class="adm-trader-meta">Leave both empty to keep maintenance active for as long as the toggle is on. A future start time delays it; an end time turns it off automatically.</span>
                    </div>
                    <div class="adm-field full">
                        <label class="adm-check"><input type="checkbox" name="signups_open" value="1" {{ old('signups_open', $signupsOpen) ? 'checked' : '' }}> Open new signups</label>
                    </div>
                    <div class="adm-field">
                        <label>Referral points</label>
                        <input type="number" name="referral_points" value="{{ old('referral_points', $referralPoints) }}" min="0" max="100000">
                    </div>
                    <div class="adm-field">
                        <label>Daily login points</label>
                        <input type="number" name="daily_points" value="{{ old('daily_points', $dailyPoints) }}" min="0" max="100000">
                    </div>
                </div>
            </div>
        </div>

        <div class="adm-card">
            <div class="adm-card-head"><i class="ri-toggle-line"></i> Feature availability</div>
            <div class="adm-card-body">
                <div class="adm-checks">
                    @foreach (\App\Models\SiteSetting::FLAGS as $flag => $label)
                        <label class="adm-check">
                            <input type="checkbox" name="flag_{{ $flag }}" value="1" {{ old('flag_' . $flag, $flags[$flag] ?? true) ? 'checked' : '' }}>
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="adm-form-actions">
            <button type="submit" class="adm-btn primary"><i class="ri-save-line"></i> Save settings</button>
            <a href="{{ $supportUrl }}" target="_blank" rel="noopener" class="adm-btn"><i class="ri-external-link-line"></i> Open support link</a>
        </div>
    </form>
@endsection
