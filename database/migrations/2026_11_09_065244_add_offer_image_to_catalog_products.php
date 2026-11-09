<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('catalog_products', function (Blueprint $table) {
            $table->string('offer_image')->nullable()->after('image');
        });

        $offerImages = [
            '2' => 'product/2026-01-1311_23_20imageAHDCarReverseCamera(1).png',
            '3' => 'product/2026-01-1306_11_22image4GLITE3CHANNELDASHCAM.png',
            '13' => 'product/2022-04-0405_58_57imagebanner2.jpg',
            '20' => 'product/2025-07-2508_37_12image72+32GBwithWirelessAppleCarplay&AndroidAuto(1).png',
            '93' => 'product/2022-09-0111_11_42imageSonyXAV-1500(390×400px).png',
            '124' => 'product/2022-09-0111_12_34imageSubaruLegacySonyXAV-AX3200(390×400px).png',
            '873' => 'product/2025-03-1208_13_20imagesolarcarperfume.png',
            '874' => 'product/2026-01-1311_29_37imageWindscreenCover.png',
            '875' => 'product/2026-01-1311_26_54image3pcConjoinedMats(1).png',
            '877' => 'product/2025-03-1207_52_52imageSpeakerSpacers(1).png',
            '878' => 'product/2025-03-1208_06_23imagephoneHOLDER.png',
            '881' => 'product/2025-07-2512_17_49image82+32GBwithWirelessAppleCarplay&AndroidAuto(1).png',
            '886' => 'product/2025-05-2712_53_14image10inchANDROID4+64.png',
            '887' => 'product/2026-01-1406_03_31image94+64GBWithSimcardSlot.png',
            '888' => 'product/2025-05-2708_00_03imageAZDOMEDRIVINGRECORDER.png',
            '889' => 'product/2025-07-2512_42_12imageTraffic1080PDashcams.png',
            '890' => 'product/2025-03-1212_33_16image69Spacers.png',
            '892' => 'product/2025-03-1212_44_30imageClothtape(1)(1)(1).png',
            '894' => 'product/2026-01-1311_34_44imageKuerlUnderseatSubwoofer.png',
            '895' => 'product/2025-05-2707_51_04imageCARDASHCAM.png',
            '896' => 'product/2025-07-2512_42_56image4kDashcam.png',
            '897' => 'product/2025-07-2514_41_25imageSeatCovers2(1).png',
            '898' => 'product/2026-01-1311_25_25imageLeatherSeatCovers.png',
            '901' => 'product/2025-07-2514_32_14imageSeatCovers1(1).png',
            '902' => 'product/2026-01-1312_19_51imageFabricSeatCovers.png',
            '903' => 'product/2026-01-1311_30_24imageCrystalGearKnob.png',
            '906' => 'product/2026-01-1405_37_05image360DegreeCameraRadio(1).png',
        ];

        foreach ($offerImages as $legacyId => $path) {
            DB::table('catalog_products')->where('legacy_id', $legacyId)->update(['offer_image' => $path]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('catalog_products', function (Blueprint $table) {
            $table->dropColumn('offer_image');
        });
    }
};
