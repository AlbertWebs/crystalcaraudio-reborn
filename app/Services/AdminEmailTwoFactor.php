<?php

namespace App\Services;

use App\Mail\AdminLoginCodeMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AdminEmailTwoFactor
{
    private const SESSION_KEY = 'admin_email_2fa_challenge';

    public function canDeliverEmail(): bool
    {
        $mailer = (string) config('mail.default');

        return $mailer !== '' && ! in_array($mailer, ['log', 'array'], true);
    }

    public function sendCode(Request $request, User $user, bool $remember = false): bool
    {
        if (! $this->canDeliverEmail()) {
            return false;
        }

        $code = (string) random_int(100000, 999999);
        $request->session()->put(self::SESSION_KEY, [
            'user_id' => $user->id,
            'email' => $user->email,
            'code_hash' => Hash::make($code),
            'remember' => $remember,
            'expires_at' => now()->addMinutes(10)->timestamp,
        ]);

        try {
            Mail::to($user->email)->send(new AdminLoginCodeMail($code));
        } catch (\Throwable $exception) {
            report($exception);
            $request->session()->forget(self::SESSION_KEY);

            return false;
        }

        return true;
    }

    /** @return array{user_id:int,email:string,code_hash:string,remember:bool,expires_at:int}|null */
    public function pendingChallenge(Request $request): ?array
    {
        $challenge = $request->session()->get(self::SESSION_KEY);
        if (! is_array($challenge) || (int) ($challenge['expires_at'] ?? 0) < now()->timestamp) {
            $request->session()->forget(self::SESSION_KEY);

            return null;
        }

        return $challenge;
    }

    public function forgetChallenge(Request $request): void
    {
        $request->session()->forget(self::SESSION_KEY);
    }
}
