<?php

namespace App\Http\Controllers;

use App\Services\AdminEmailTwoFactor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminProfileController extends Controller
{
    public function edit(Request $request, AdminEmailTwoFactor $twoFactor)
    {
        return view('admin.profile', [
            'user' => $request->user(),
            'emailDeliveryReady' => $twoFactor->canDeliverEmail(),
        ]);
    }

    public function updateSecurity(Request $request, AdminEmailTwoFactor $twoFactor)
    {
        $data = $request->validate([
            'admin_email_2fa_enabled' => ['required', 'boolean'],
        ]);

        $user = $request->user();
        $enabled = (bool) $data['admin_email_2fa_enabled'];
        if ($enabled && ! $twoFactor->canDeliverEmail()) {
            return redirect()->route('admin.profile')->withErrors([
                'admin_email_2fa_enabled' => 'Configure an email delivery mailer before enabling this setting. The current mailer does not deliver messages to your inbox.',
            ]);
        }

        $user->forceFill(['admin_email_2fa_enabled' => $enabled])->save();

        DB::table('admin_activity_logs')->insert([
            'user_id' => $user->id,
            'action' => 'profile.email_2fa_updated',
            'subject_type' => 'user',
            'subject_id' => (string) $user->id,
            'summary' => 'Email two-factor authentication '.($enabled ? 'enabled' : 'disabled').' for '.$user->email.'.',
            'ip_address' => $request->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.profile')->with('success', $enabled
            ? 'Email two-factor authentication is on. The next sign-in will ask for a code sent to your email.'
            : 'Email two-factor authentication is off.');
    }
}
