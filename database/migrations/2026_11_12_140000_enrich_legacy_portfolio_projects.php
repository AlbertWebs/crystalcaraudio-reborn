<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $projects = [
            [
                'legacy_source_id' => '2',
                'title' => 'Nissan March 2015 Car Audio Upgrade',
                'slug' => 'nissan-march-2015-car-audio-upgrade',
                'vehicle_make' => 'Nissan',
                'vehicle_model' => 'March',
                'vehicle_year' => 2015,
                'upgrade_type' => 'Android car radio and speaker upgrade',
                'project_date' => '2022-09-11',
                'service_area' => 'Westlands, Nairobi',
                'client_name' => 'Ernesto',
                'description' => <<<'MD'
Crystal Car Audio upgraded Ernesto’s 2015 Nissan March with a modern seven-inch Android radio and Sony oval speakers fitted in a cabinet at his Westlands residence. The brief was to update the in-car entertainment and improve the sound while leaving the factory speaker setup intact.

## Radio and speaker upgrade

The new Android head unit adds a modern touchscreen interface to the Nissan March. The speaker upgrade uses a pair of [Sony XS-GTF6939 oval car speakers](/product/sony-speakers-xs-gtf6939), selected for the project and fitted in a cabinet. Together, the additions refresh the listening experience while respecting the customer’s preference around the original speaker installation.

Explore the current [car radio range](/products/car-radio) if you are planning a similar Nissan March upgrade. Radio size, fascia, wiring and vehicle trim vary by model year, so confirm fitment with Crystal Car Audio before ordering.

Ernesto was pleased with the completed car audio upgrade. Crystal Car Audio helps drivers in Nairobi plan radio and speaker installations around their vehicle and listening goals.
MD,
                'images' => null,
                'video_path' => null,
                'is_published' => true,
            ],
            [
                'legacy_source_id' => '3',
                'title' => 'Mazda Demio 2012 Car Radio upgrade',
                'slug' => 'mazda-demio-2012-car-radio-upgrade',
                'vehicle_make' => 'Mazda',
                'vehicle_model' => 'Demio',
                'vehicle_year' => 2012,
                'upgrade_type' => '9-inch Android radio and reverse camera upgrade',
                'project_date' => '2022-09-06',
                'service_area' => 'Parklands, Nairobi',
                'client_name' => 'Horst',
                'description' => <<<'MD'
Crystal Car Audio upgraded Horst’s 2012 Mazda Demio with a nine-inch Android radio and a reversing camera at his home in Parklands. He wanted a replacement system with Google Maps, Bluetooth hands-free calling and messaging support for his iPhone, as well as a camera to make reversing easier.

## Android radio and reversing camera

The vehicle-specific Android radio brings navigation and smartphone connectivity to the Demio’s dashboard. The reversing camera adds a rear view when parking. The installation was planned around the 2012 model so the radio and camera could work together as a practical everyday upgrade.

Browse our [car radio category](/products/car-radio) and [in-car accessories](/products/led-lights) for current options. Product availability and vehicle fitment can change; share your Demio’s year and dashboard details with Crystal Car Audio before choosing a replacement radio or camera.

Crystal Car Audio provides car radio and camera installation guidance in Nairobi, including fitment checks for Mazda Demio upgrades.
MD,
                'images' => null,
                'video_path' => null,
                'is_published' => true,
            ],
            [
                'legacy_source_id' => '4',
                'title' => 'Subaru Forester XT Car Audio Install',
                'slug' => 'subaru-forester-xt-car-audio-install',
                'vehicle_make' => 'Subaru',
                'vehicle_model' => 'Forester XT',
                'vehicle_year' => 2015,
                'upgrade_type' => 'Apple CarPlay, Android Auto and under-seat subwoofer upgrade',
                'project_date' => '2022-06-13',
                'service_area' => null,
                'client_name' => 'Lawrence',
                'description' => <<<'MD'
Crystal Car Audio upgraded Lawrence’s 2015 Subaru Forester XT with a Sony XAV-AX3200 multimedia receiver and a Pioneer under-seat subwoofer. The project paired smartphone connectivity with added bass while keeping the equipment choice specific to the customer’s requested setup.

## Sony multimedia receiver and Pioneer bass upgrade

The [Sony XAV-AX3200 car radio](/product/car-radio-xav-ax3200-with-android-aut) supports Apple CarPlay and Android Auto for compatible phones. A [Pioneer TS-WX400DA under-seat subwoofer](/product/ts-wx400da-pioneer-underseat-sub) adds low-frequency output in a compact format designed to fit beneath a seat, subject to vehicle clearance and installation requirements.

Explore the [car radio range](/products/car-radio) and [car speakers and subwoofers](/products/car-speakers) to compare current equipment for a similar Forester upgrade. Check the selected radio’s wiring, dashboard fit and subwoofer clearance with Crystal Car Audio before ordering.

This project shows how a multimedia radio and compact subwoofer can be combined in one Subaru Forester XT car audio installation.
MD,
                'images' => null,
                'video_path' => null,
                'is_published' => true,
            ],
        ];

        foreach ($projects as $project) {
            $values = $project + ['created_at' => now()];
            $values['updated_at'] = now();

            DB::table('portfolio_projects')->updateOrInsert(
                ['legacy_source_id' => $project['legacy_source_id']],
                $values
            );
        }
    }

    public function down(): void
    {
        DB::table('portfolio_projects')->whereIn('legacy_source_id', ['2', '3', '4'])->delete();
    }
};
