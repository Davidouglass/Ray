<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        if (! \App\Models\SiteSetting::bool('signups_open', true)) {
            throw ValidationException::withMessages([
                'email' => 'New signups are temporarily closed.',
            ]);
        }

        $data = $request->validate([
            'username' => ['required', 'string', 'min:3', 'max:40'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::create([
            'name' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $ref = strtoupper((string) $request->input('ref', ''));
        if ($ref !== '') {
            $referrer = User::where('referral_code', $ref)->first();
            if ($referrer && $referrer->id !== $user->id) {
                $user->forceFill(['referred_by' => $referrer->id])->save();
                $points = (int) \App\Models\SiteSetting::get('referral_points', '250');
                app(\App\Services\RewardsService::class)->award($referrer, 'referral', $points, ['new_user' => $user->id]);
            }
        }

        $user->forceFill(['last_login_at' => now()])->save();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended('/discover');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt(['email' => $data['email'], 'password' => $data['password']], $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $user = Auth::user();

        if ($user && ! $user->isActive()) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'This account is suspended.' . ($user->suspended_reason ? ' Reason: ' . $user->suspended_reason : ''),
            ]);
        }

        $user?->forceFill(['last_login_at' => now()])->save();

        $request->session()->regenerate();

        return redirect()->intended('/discover');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
