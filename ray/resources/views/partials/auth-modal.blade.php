@php
    $mode = $authMode ?? 'login';
    $isSignup = $mode === 'signup';
@endphp
<div class="auth-modal" id="authModal">
    <div class="auth-modal-backdrop" onclick="window.location='/'"></div>
    <div class="auth-modal-card">
        <form class="am-form" method="POST" action="{{ $mode === 'login' ? '/login' : '/register' }}">
            @csrf

            <div class="am-head">
                <div class="am-head-left"></div>
                <div class="am-head-center">
                    <span class="am-head-title">{{ $mode === 'login' ? 'Login' : 'Sign Up' }}</span>
                </div>
                <div class="am-head-right">
                    <a href="/" class="am-close" aria-label="Close">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </a>
                </div>
            </div>

            @if ($errors->any())
                <div class="am-error">{{ $errors->first() }}</div>
            @endif

            <div class="am-fields">
                <div class="am-field">
                    <span class="am-label">Email</span>
                    <div class="am-input-wrap">
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="Enter email" autocomplete="email" @if($mode==='login') autofocus @endif>
                    </div>
                </div>

                <div class="am-field">
                    <span class="am-label">Password</span>
                    <div class="am-input-wrap">
                        <input type="password" name="password" placeholder="Enter password" autocomplete="{{ $mode === 'login' ? 'current-password' : 'new-password' }}" class="am-pw">
                        <button type="button" class="am-eye" aria-label="Show password" onclick="var i=this.previousElementSibling;i.type=i.type==='password'?'text':'password';">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>

                @if ($mode === 'login')
                <div class="am-forgot"><a href="/login">Forgot password?</a></div>
                @endif

                @if ($mode === 'signup')
                <div class="am-field">
                    <div class="am-input-wrap">
                        <input type="text" name="invite" value="{{ old('invite') }}" placeholder="Invite code (optional)" autocomplete="off">
                    </div>
                </div>
                @endif
            </div>

            <div class="am-actions">
                <button type="submit" class="am-submit">
                    <span>{{ $mode === 'login' ? 'Login' : 'Sign Up' }}</span>
                </button>
            </div>

            <div class="am-divider">
                <div class="am-line"></div>
                <span>{{ $mode === 'login' ? 'Or Login' : 'Or Sign Up' }}</span>
                <div class="am-line"></div>
            </div>

            <div class="am-socials">
                <button type="button" class="am-social">
                    <img class="am-social-ico" alt="Google" loading="lazy" src="https://axiom-assets-v2.axiom-cdn.io/images/google-logo.svg">
                    <span class="am-social-label">Continue with Google</span>
                </button>
            </div>

            <div class="am-socials">
                <button type="button" class="am-social am-social-phantom">
                    <img class="am-social-ico" alt="Phantom" loading="lazy" src="https://axiom-assets-v2.axiom-cdn.io/images/phantom-purple.svg">
                    <span class="am-social-label">Connect with Phantom</span>
                </button>
            </div>

            <span class="am-switch">
                {{ $mode === 'login' ? "Don't have an account?" : 'Already have an account?' }}
                <a href="{{ $mode === 'login' ? '/signup' : '/login' }}">{{ $mode === 'login' ? 'Sign Up' : 'Login' }}</a>
            </span>

            @if ($mode === 'signup')
            <span class="am-terms">
                By creating an account, you agree to Veridian's
                <a href="/privacy">Privacy Policy</a> and <a href="/terms">Terms of Service</a>
            </span>
            @endif
        </form>
    </div>
</div>
