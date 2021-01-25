<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

//Internal Uses
use Codificar\Geolocation\Models\GeolocationSettings;

class AddDirectionsSettings extends Migration
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

        $setting = GeolocationSettings::where(['key' => 'directions_provider'])->first();
        if(!$setting){
            GeolocationSettings::updateOrCreate(array('key' => 'directions_provider'), array(
                'value' => 'google_maps', 'page' => 1, 'category' => $settingCategory
            ));
        }

        $setting = GeolocationSettings::where(['key' => 'directions_key'])->first();
        if(!$setting){
            GeolocationSettings::updateOrCreate(array('key' => 'directions_key'), array(
                'value' => $defaultKey, 'page' => 1, 'category' => $settingCategory
            ));
        }

        $setting = GeolocationSettings::where(['key' => 'directions_url'])->first();
        if(!$setting){
            GeolocationSettings::updateOrCreate(array('key' => 'directions_url'), array(
                'value' => '', 'page' => 1, 'category' => $settingCategory
            ));
        }

        $setting = GeolocationSettings::where(['key' => 'directions_url'])->first();
        if(!$setting){
            GeolocationSettings::updateOrCreate(array('key' => 'directions_url'), array(
                'value' => '', 'page' => 1, 'category' => $settingCategory
            ));
        }

        // Redundancy
        $setting = GeolocationSettings::where(['key' => 'directions_redundancy_rule'])->first();
        if(!$setting){
            GeolocationSettings::updateOrCreate(array('key' => 'directions_redundancy_rule'), array(
                'value' => '0', 'page' => 1, 'category' => $settingCategory
            ));
        }

        $setting = GeolocationSettings::where(['key' => 'directions_provider_redundancy'])->first();
        if(!$setting){
            GeolocationSettings::updateOrCreate(array('key' => 'directions_provider_redundancy'), array(
                'value' => 'google_maps', 'page' => 1, 'category' => $settingCategory
            ));
        }

        $setting = GeolocationSettings::where(['key' => 'directions_key_redundancy'])->first();
        if(!$setting){
            GeolocationSettings::updateOrCreate(array('key' => 'directions_key_redundancy'), array(
                'value' => '', 'page' => 1, 'category' => $settingCategory
            ));
        }

        $setting = GeolocationSettings::where(['key' => 'directions_url_redundancy'])->first();
        if(!$setting){
            GeolocationSettings::updateOrCreate(array('key' => 'directions_url_redundancy'), array(
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
       
    }
}
