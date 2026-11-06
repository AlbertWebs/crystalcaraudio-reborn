<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class ActivateLegacyAccounts extends Command
{
    protected $signature = 'legacy:activate-accounts';
    protected $description = 'Enable sign-in for legacy customers while keeping their archive encrypted.';

    public function handle(): int
    {
        $activated = 0;
        DB::table('legacy_archive_records')->where('source_table','users')->orderBy('id')->chunkById(100, function ($rows) use (&$activated) {
            foreach ($rows as $row) {
                $user = json_decode(Crypt::decryptString($row->payload), true) ?: [];
                if (! filter_var($user['email'] ?? null, FILTER_VALIDATE_EMAIL) || empty($user['password'])) continue;
                DB::table('users')->updateOrInsert(['email' => $user['email']], [
                    'name' => $user['name'] ?: 'Crystal customer', 'password' => $user['password'],
                    'phone' => $user['mobile'] ?? null, 'address' => $user['address'] ?? null,
                    'legacy_customer_id' => (string) ($user['id'] ?? $row->source_id),
                    'email_verified_at' => $user['email_verified_at'] ?? null,
                    'updated_at' => now(), 'created_at' => $user['created_at'] ?? now(),
                ]);
                $activated++;
            }
        });
        $this->info("Activated {$activated} legacy customer accounts.");
        return self::SUCCESS;
    }
}
