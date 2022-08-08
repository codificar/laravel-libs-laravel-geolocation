<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use Codificar\Geolocation\Models\GeolocationSettings;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $settingCategory = GeolocationSettings::getGeolocationCategory();
        $defaultKey = GeolocationSettings::getGoogleMapsApiKey();

        if (!Settings::where('key', 'directionsEstimateRule')->first()) {
            Settings::create([
                "key" => "directionsEstimateRule",
                "value" => '0',
                "page" => '1',
                "category" => "9",
                "sub_category" => "0"
            ]);
        }

        if (!GeolocationSettings::where(['key' => 'directions_provider_estimate'])->first()) {
            GeolocationSettings::updateOrCreate(array('key' => 'directions_provider_estimate'), array(
                'value' => 'google_maps', 'page' => 1, 'category' => $settingCategory
            ));
        }

        if (!GeolocationSettings::where(['key' => 'directions_key_estimate'])->first()) {
            GeolocationSettings::updateOrCreate(array('key' => 'directions_key_estimate'), array(
                'value' => '', 'page' => 1, 'category' => $settingCategory
            ));
        }

        if (!GeolocationSettings::where(['key' => 'directions_url_estimate'])->first()) {
            GeolocationSettings::updateOrCreate(array('key' => 'directions_url_estimate'), array(
                'value' => '', 'page' => 1, 'category' => $settingCategory
            ));
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
