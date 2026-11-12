<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $products = [
            'KFC-W3010' => [
                'meta_title' => 'Kenwood KFC-W3010 12-Inch Subwoofer | Crystal Car Audio',
                'meta_description' => 'Shop the Kenwood KFC-W3010 12-inch subwoofer at Crystal Car Audio in Nairobi. It handles 200 W RMS and up to 1,000 W peak for a fuller low end. Ask us about enclosure fitment.',
                'description' => <<<'HTML'
<p>The Kenwood KFC-W3010 is a 12-inch car subwoofer built to add deeper bass to a vehicle sound system. Kenwood rates it at 200 W RMS and up to 1,000 W peak power. Its rigid cone design is intended to deliver controlled low frequencies when paired with a suitable amplifier and enclosure.</p>
<p>Enclosure type, available boot space, amplifier output and wiring all affect the final result. The KFC-W3010 was used in the Toyota Axio full system upgrade shown on this page, fitted in a space-saving cabinet. Ask Crystal Car Audio to check the right enclosure and amplifier match for your vehicle before ordering.</p>
<p>Crystal Car Audio supplies car audio equipment in Nairobi, with delivery available across Kenya. Contact our team for current stock, installation options and fitment guidance.</p>
HTML,
                'specifications' => [
                    ['title' => 'Speaker size', 'value' => '12 in (300 mm)'],
                    ['title' => 'Rated power', 'value' => '200 W RMS'],
                    ['title' => 'Peak power', 'value' => '1,000 W'],
                    ['title' => 'Type', 'value' => 'Car subwoofer'],
                ],
            ],
            'GM-E7004' => [
                'meta_title' => 'Pioneer GM-E7004 4-Channel Amplifier | Crystal Car Audio',
                'meta_description' => 'Shop the Pioneer GM-E7004 four-channel car amplifier in Nairobi. Class AB power, variable filters and bridgeable channels help build a balanced car audio system.',
                'description' => <<<'HTML'
<p>The Pioneer GM-E7004 is a four-channel Class AB car amplifier for powering a set of speakers or a combination of speakers and other compatible components. Published ratings are 70 W RMS per channel at 4 ohms, 95 W RMS per channel at 2 ohms, or 190 W RMS x 2 in bridged mode at 4 ohms.</p>
<p>Its variable high-pass and low-pass filters and bass boost provide adjustment for a properly matched system. Check speaker impedance, wiring and the selected amplifier mode before installation; bridged operation requires the correct speaker connection and load.</p>
<p>This Pioneer amplifier was part of the Toyota Axio full system upgrade featured on this page. Crystal Car Audio can help check your system components and installation requirements in Nairobi, with delivery available across Kenya.</p>
HTML,
                'specifications' => [
                    ['title' => 'Channels', 'value' => '4-channel'],
                    ['title' => 'Amplifier class', 'value' => 'Class AB'],
                    ['title' => 'Power at 4 ohms', 'value' => '70 W x 4 RMS'],
                    ['title' => 'Power at 2 ohms', 'value' => '95 W x 4 RMS'],
                    ['title' => 'Bridged power', 'value' => '190 W x 2 RMS at 4 ohms'],
                    ['title' => 'Frequency response', 'value' => '10 Hz–40 kHz'],
                ],
            ],
            'TS-S20' => [
                'meta_title' => 'Pioneer TS-S20 Tweeters | Crystal Car Audio Nairobi',
                'meta_description' => 'Shop Pioneer TS-S20 20 mm tweeters at Crystal Car Audio in Nairobi. Add high-frequency detail to a compatible car audio setup and ask our team about installation.',
                'description' => <<<'HTML'
<p>The Pioneer TS-S20 is a compact 20 mm car tweeter designed to reproduce the high-frequency detail in music. It can complement compatible door speakers by placing treble higher in the vehicle cabin. The catalogue rating for this model is 50 W RMS and 200 W maximum input.</p>
<p>Confirm the crossover, impedance, wiring and mounting position for your audio system before connecting tweeters. Correct filtering and placement help protect the driver and balance the sound with the rest of the speakers.</p>
<p>The TS-S20 was included in the Toyota Axio full system upgrade on this page. Crystal Car Audio offers product and fitment guidance in Nairobi, Kenya; contact our team to check compatibility with your setup.</p>
HTML,
                'specifications' => [
                    ['title' => 'Tweeter size', 'value' => '20 mm'],
                    ['title' => 'Rated power', 'value' => '50 W RMS'],
                    ['title' => 'Maximum input', 'value' => '200 W'],
                    ['title' => 'Frequency range', 'value' => '3 kHz–26 kHz'],
                    ['title' => 'Impedance', 'value' => '8 ohms'],
                ],
            ],
            'KFC-S1666' => [
                'meta_title' => 'Kenwood KFC-S1666 6.5-Inch Speakers | Crystal Car Audio',
                'meta_description' => 'Shop Kenwood KFC-S1666 6.5-inch car speakers at Crystal Car Audio. 300 W peak handling, 30 W RMS and clear two-way sound. Ask us to check door fitment in Nairobi.',
                'description' => <<<'HTML'
<p>The Kenwood KFC-S1666 is a 6.5-inch (160 mm) two-way coaxial car speaker for replacing or upgrading compatible factory door speakers. Kenwood's KFC-1666S specification lists 30 W RMS rated input and 300 W peak input, with a 4-ohm impedance.</p>
<p>Check the vehicle's speaker opening, mounting depth, connector and required adapter before ordering. Fitment varies by vehicle and door trim. This speaker was used as part of the Toyota Axio full system upgrade featured on this page.</p>
<p>Crystal Car Audio can help confirm compatibility and installation options in Nairobi, Kenya, and arrange delivery across the country.</p>
HTML,
                'specifications' => [
                    ['title' => 'Speaker size', 'value' => '6.5 in (160 mm)'],
                    ['title' => 'Speaker type', 'value' => 'Two-way coaxial'],
                    ['title' => 'Rated power', 'value' => '30 W RMS'],
                    ['title' => 'Peak power', 'value' => '300 W'],
                    ['title' => 'Impedance', 'value' => '4 ohms'],
                    ['title' => 'Frequency response', 'value' => '40 Hz–22 kHz'],
                ],
                'image' => 'product/Kenwood-Speakers,6.5_-300W-KFC-S1666-thumbnail.png',
                'gallery' => [
                    'product/Kenwood-Speakers,6.5_-300W-KFC-S1666-thumbnail.png',
                    'product/Kenwood-Speakers,6.5_-300W-KFC-S1666-fb_pixels.png',
                ],
            ],
        ];

        foreach ($products as $sku => $product) {
            $updates = [
                'meta_title' => $product['meta_title'],
                'meta_description' => $product['meta_description'],
                'description' => $product['description'],
                'specifications' => json_encode($product['specifications'], JSON_THROW_ON_ERROR),
                'updated_at' => now(),
            ];

            if (isset($product['image'])) {
                $updates['image'] = $product['image'];
                $updates['gallery'] = json_encode($product['gallery'], JSON_THROW_ON_ERROR);
            }

            DB::table('catalog_products')->where('sku', $sku)->update($updates);
        }

        DB::table('portfolio_projects')->updateOrInsert(
            ['legacy_source_id' => '5'],
            [
                'title' => 'Toyota Axio Full System Upgrade',
                'slug' => 'toyota-axio-full-system-upgrade',
                'vehicle_make' => 'Toyota',
                'vehicle_model' => 'Axio',
                'vehicle_year' => 2009,
                'work_order' => null,
                'upgrade_type' => 'Full car audio system upgrade',
                'project_date' => '2022-09-11',
                'service_area' => 'Nairobi, Kenya',
                'client_name' => 'Tony Odhiambo',
                'description' => <<<'MD'
This 2009 Toyota Axio received a full car audio system upgrade by Crystal Car Audio in Nairobi. The setup combines a dedicated subwoofer and amplifier with upgraded cabin speakers and tweeters, bringing together low-frequency output and clearer detail across the audio range.

## Equipment installed

- **Bass:** [Kenwood KFC-W3010 12-inch subwoofer](/product/kenwood-bass-speaker-kfc-w3010), installed in a space-saving cabinet.
- **Amplification:** [Pioneer GM-E7004 four-channel amplifier](/product/pioneer-amplifier-booster-gm-e7004).
- **High frequencies:** [Pioneer TS-S20 tweeters](/product/pioneer-ts-s20-tweeters-200-watts).
- **Door speakers:** [Kenwood KFC-S1666 6.5-inch speakers](/product/kenwood-speakers65-300w-kfc-s1666).

The components are linked to their individual product pages so you can review specifications, current availability and fitment information. Product availability and pricing may change over time; contact Crystal Car Audio to confirm the current options for your vehicle.

## Toyota Axio car audio fitment

Vehicle trim and existing wiring can affect speaker fitment and amplifier connections. Share your Toyota Axio year and system requirements with our team before ordering so we can help check compatibility and discuss installation in Nairobi.
MD,
                'images' => json_encode([
                    'uploads/portfolio/2022-09-1415_41_17imageToyotaAXIO2009FullCarSoundSystem.png',
                    'uploads/portfolio/2022-09-1415_41_17imageKenwoodKFC-W3010FittedinaSpaceSavingCabinet.png',
                ], JSON_THROW_ON_ERROR),
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('portfolio_projects')->where('legacy_source_id', '5')->delete();

        // Content updates are intentionally not reversed: restoring incomplete legacy copy
        // would also remove historical product imagery and the verified specifications.
    }
};
