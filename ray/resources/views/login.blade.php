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
        <h1 class="auth-title">Welcome back</h1>
        <p class="auth-sub">Sign in to your Veridian account to trade, track portfolios and manage rewards.</p>

        <form class="auth-form" action="#" method="post" autocomplete="on">
            <label class="field" for="auth-email">
                <span class="field-label">Email</span>
                <input id="auth-email" type="email" name="email" placeholder="you@example.com" required autofocus>
            </label>
            <label class="field" for="auth-password">
                <span class="field-label">Password</span>
                <input id="auth-password" type="password" name="password" placeholder="Enter your password" required>
            </label>
            <div class="auth-row">
                <label class="check">
                    <input type="checkbox" name="remember">
                    <span>Remember me</span>
                </label>
                <a href="#" class="auth-link">Forgot password?</a>
            </div>
            <button type="submit" class="btn btn-signup auth-submit">Sign in</button>
        </form>

        <p class="auth-switch">New to Veridian? <a href="/signup">Create an account</a></p>
    </div>
</div>
@endsection