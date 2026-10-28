@extends('layout')

@section('content')
<div class="auth-shell">
    <div class="auth-glow" aria-hidden="true"></div>
    <div class="auth-card">
        <div class="auth-mark">
            <svg width="40" height="40" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M24.14 17.39H11.86L18 7l6.14 10.39Z" fill="currentColor"/>
                <path d="M31 29H5l4.97-8.41h16.06L31 29Z" fill="currentColor"/>
            </svg>
        </div>
        <h1 class="auth-title">Create your account</h1>
        <p class="auth-sub">Join Veridian to trade Solana, BSC and Ethereum with rewards on every trade.</p>

        <form class="auth-form" action="#" method="post" autocomplete="on">
            <label class="field" for="su-name">
                <span class="field-label">Username</span>
                <input id="su-name" type="text" name="name" placeholder="Choose a username" required autofocus>
            </label>
            <label class="field" for="su-email">
                <span class="field-label">Email</span>
                <input id="su-email" type="email" name="email" placeholder="you@example.com" required>
            </label>
            <label class="field" for="su-password">
                <span class="field-label">Password</span>
                <input id="su-password" type="password" name="password" placeholder="Create a password" required>
            </label>
            <label class="check su-check">
                <input type="checkbox" name="terms" required>
                <span>I agree to the <a href="#" class="auth-link">Terms of Service</a> and <a href="#" class="auth-link">Privacy Policy</a></span>
            </label>
            <button type="submit" class="btn btn-signup auth-submit">Create account</button>
        </form>

        <p class="auth-switch">Already have an account? <a href="/login">Sign in</a></p>
    </div>
</div>
@endsection