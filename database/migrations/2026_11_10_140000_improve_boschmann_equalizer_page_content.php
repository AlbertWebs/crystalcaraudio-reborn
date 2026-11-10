<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private string $slug = 'boschmann-equalizer-eqx-75pro';

    private string $originalDescription = <<<'HTML'
<p>Boschmann EQX-75PRO Is a proffesional parametric 7 band Equalizer used to control sound output in an audio setup.</p>rnrn<p>Bochmann Audio Equalizer features 7 front panel controls namely ;</p>rnrn<p>Gain Control - Use&nbsp;to adjust the sound output level .</p>rnrn<p>Fader Control - Adjust Volume between the front and rear speakers in a four speaker system.</p>rnrn<p>Input Selector - Use to switch between AUX and Radio input</p>rnrn<p>Backmination Switch - Selects the led light either Red or Green.</p>rnrn<p>Level Switch - Control Subwoofer Level</p>rnrn<p>Sub Frequency - Adjust subwoofer frequency from 30Hz to 120Hz</p>rnrn<p>Parametric Equalizer Controls - Control the frequency response of the boost or cut with the 7 band controls ; 50HZ,125HZ, 315Hz, 750Hz, 2.2kHZ, 6KHZ,12KHz</p>
HTML;

    public function up(): void
    {
        DB::table('catalog_products')->where('slug', $this->slug)->update([
            'meta_title' => 'Boschmann EQX-75PRO 7-Band Equalizer | Crystal Car Audio',
            'meta_description' => 'Shop the Boschmann EQX-75PRO 7-band equalizer in Nairobi. Tune seven bands, balance front and rear speakers, and set subwoofer frequency from 30–120 Hz.',
            'description' => <<<'HTML'
<p>The Boschmann EQX-75PRO is a seven-band parametric car audio equalizer that gives you hands-on control over your system’s frequency balance. Its seven bands cover 50 Hz, 125 Hz, 315 Hz, 750 Hz, 2.2 kHz, 6 kHz and 12 kHz.</p>
<p>Use the gain control to adjust output level and the fader to balance front and rear speakers in a four-speaker setup. The input selector switches between AUX and radio.</p>
<p>A separate subwoofer level control and 30–120 Hz frequency adjustment help you tune bass output to your system. The front-panel illumination can be set to red or green.</p>
<p>Before ordering, check that your audio system’s inputs, outputs, wiring and available mounting space are compatible. The EQX-75PRO operates on 11–15 V negative-ground power. Contact Crystal Car Audio for fitment advice for your vehicle.</p>
<p>What’s included: Boschmann EQX-75PRO user manual.</p>
HTML,
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('catalog_products')->where('slug', $this->slug)->update([
            'meta_title' => null,
            'meta_description' => null,
            'description' => $this->originalDescription,
            'updated_at' => now(),
        ]);
    }
};
