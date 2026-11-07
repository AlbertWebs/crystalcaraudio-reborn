<?php

namespace Tests\Feature;

use App\Mail\AdminLoginCodeMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminEmailTwoFactorTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_two_factor_is_off_by_default_and_admin_can_sign_in_normally(): void
    {
        config(['store.admin_email' => 'admin@crystalcaraudio.com']);
        $admin = User::factory()->create([
            'email' => 'admin@crystalcaraudio.com',
            'password' => 'StrongPassword!123',
        ]);

        $this->assertFalse($admin->fresh()->admin_email_2fa_enabled);

        $this->post(route('admin.login.post'), [
            'email' => $admin->email,
            'password' => 'StrongPassword!123',
        ])->assertRedirect(route('admin.home'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_enabled_email_two_factor_requires_the_email_code_before_admin_access(): void
    {
        Mail::fake();
        config(['mail.default' => 'smtp']);
        config(['store.admin_email' => 'admin@crystalcaraudio.com']);
        $admin = User::factory()->create([
            'email' => 'admin@crystalcaraudio.com',
            'password' => 'StrongPassword!123',
            'admin_email_2fa_enabled' => true,
        ]);
        $code = null;

        $this->post(route('admin.login.post'), [
            'email' => $admin->email,
            'password' => 'StrongPassword!123',
        ])->assertRedirect(route('admin.two-factor'));

        $this->assertGuest();
        Mail::assertSent(AdminLoginCodeMail::class, function (AdminLoginCodeMail $mail) use ($admin, &$code): bool {
            $code = $mail->code;

            return $mail->hasTo($admin->email);
        });

        $this->get(route('admin.two-factor'))->assertOk()->assertSee('Check your email');
        $this->post(route('admin.two-factor.verify'), ['code' => $code])
            ->assertRedirect(route('admin.home'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_admin_can_enable_email_two_factor_from_profile(): void
    {
        config(['mail.default' => 'smtp']);
        config(['store.admin_email' => 'admin@crystalcaraudio.com']);
        $admin = User::factory()->create(['email' => 'admin@crystalcaraudio.com']);

        $this->actingAs($admin)->get(route('admin.profile'))
            ->assertOk()
            ->assertSee('Email two-factor authentication');

        $this->actingAs($admin)->patch(route('admin.profile.security'), ['admin_email_2fa_enabled' => '1'])
            ->assertRedirect(route('admin.profile'));

        $this->assertTrue($admin->fresh()->admin_email_2fa_enabled);
        $this->assertDatabaseHas('admin_activity_logs', [
            'user_id' => $admin->id,
            'action' => 'profile.email_2fa_updated',
        ]);
    }

    public function test_admin_cannot_enable_email_two_factor_when_the_mailer_only_logs_messages(): void
    {
        config(['mail.default' => 'log']);
        config(['store.admin_email' => 'admin@crystalcaraudio.com']);
        $admin = User::factory()->create(['email' => 'admin@crystalcaraudio.com']);

        $this->actingAs($admin)->patch(route('admin.profile.security'), ['admin_email_2fa_enabled' => '1'])
            ->assertRedirect(route('admin.profile'))
            ->assertSessionHasErrors('admin_email_2fa_enabled');

        $this->assertFalse($admin->fresh()->admin_email_2fa_enabled);
    }
}
