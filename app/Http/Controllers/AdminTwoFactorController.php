<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AdminEmailTwoFactor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminTwoFactorController extends Controller
{
    public function __construct(private readonly AdminEmailTwoFactor $twoFactor) {}

    public function show(Request $request)
    {
        $challenge = $this->twoFactor->pendingChallenge($request);
        if (! $challenge) {
            return redirect()->route('admin.login')->withErrors(['email' => 'Your sign-in code expired. Please sign in again.']);
        }

        $user = User::query()->find($challenge['user_id']);
        if (! $user || ! $user->admin_email_2fa_enabled || ! $user->can('manage-store')) {
            $this->twoFactor->forgetChallenge($request);

            return redirect()->route('admin.login')->withErrors(['email' => 'Please sign in again to continue.']);
        }

        return view('admin.two-factor', ['maskedEmail' => $this->maskEmail($challenge['email'])]);
    }

    public function verify(Request $request)
    {
        $data = $request->validate(['code' => ['required', 'digits:6']]);
        $challenge = $this->twoFactor->pendingChallenge($request);
        if (! $challenge) {
            return redirect()->route('admin.login')->withErrors(['email' => 'Your sign-in code expired. Please sign in again.']);
        }

        $user = User::query()->find($challenge['user_id']);
        if (! $user || ! $user->admin_email_2fa_enabled || ! $user->can('manage-store') || $user->email !== $challenge['email']) {
            $this->twoFactor->forgetChallenge($request);

            return redirect()->route('admin.login')->withErrors(['email' => 'Please sign in again to continue.']);
        }

        if (! Hash::check($data['code'], $challenge['code_hash'])) {
            return back()->withErrors(['code' => 'That code is not correct. Check your email and try again.']);
        }

        $this->twoFactor->forgetChallenge($request);
        Auth::login($user, (bool) $challenge['remember']);
        $request->session()->regenerate();

        return redirect()->intended(route('admin.home'));
    }

    public function resend(Request $request)
    {
        $challenge = $this->twoFactor->pendingChallenge($request);
        $user = $challenge ? User::query()->find($challenge['user_id']) : null;
        if (! $challenge || ! $user || ! $user->admin_email_2fa_enabled || ! $user->can('manage-store') || $user->email !== $challenge['email']) {
            $this->twoFactor->forgetChallenge($request);

            return redirect()->route('admin.login')->withErrors(['email' => 'Please sign in again to request a new code.']);
        }

        if (! $this->twoFactor->sendCode($request, $user, (bool) $challenge['remember'])) {
            return back()->withErrors(['code' => 'We could not send the email right now. Please try again shortly.']);
        }

        return back()->with('status', 'A new verification code has been sent.');
    }

    private function maskEmail(string $email): string
    {
        [$name, $domain] = array_pad(explode('@', $email, 2), 2, '');
        $visible = Str::substr($name, 0, 1);

        return $visible.str_repeat('•', max(2, Str::length($name) - 1)).'@'.$domain;
    }
}
