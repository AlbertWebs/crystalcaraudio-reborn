<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class SecureLegacyArchive extends Command
{
    protected $signature = 'legacy:secure-archive';
    protected $description = 'Encrypt imported customer and order archive payloads at rest.';

    public function handle(): int
    {
        $secured = 0;
        DB::table('legacy_archive_records')->orderBy('id')->chunkById(200, function ($rows) use (&$secured) {
            foreach ($rows as $row) {
                if (! str_starts_with((string) $row->payload, '{')) continue;
                DB::table('legacy_archive_records')->where('id', $row->id)->update([
                    'payload' => Crypt::encryptString($row->payload), 'updated_at' => now(),
                ]);
                $secured++;
            }
        });
        $this->info("Secured {$secured} archived records.");
        return self::SUCCESS;
    }
}
