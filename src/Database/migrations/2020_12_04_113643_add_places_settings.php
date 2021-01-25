<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

//Internal Uses
use Codificar\Geolocation\Models\GeolocationSettings;

class AddPlacesSettings extends Migration
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

        $setting = GeolocationSettings::where(['key' => 'places_provider'])->first();
        if(!$setting){
            GeolocationSettings::updateOrCreate(array('key' => 'places_provider'), array(
                'value' => 'google_maps', 'page' => 1, 'category' => $settingCategory
            ));
        }

        $setting = GeolocationSettings::where(['key' => 'places_key'])->first();
        if(!$setting){
            GeolocationSettings::updateOrCreate(array('key' => 'places_key'), array(
                'value' => $defaultKey, 'page' => 1, 'category' => $settingCategory
            ));
        }

        $setting = GeolocationSettings::where(['key' => 'places_url'])->first();
        if(!$setting){
            GeolocationSettings::updateOrCreate(array('key' => 'places_url'), array(
                'value' => '', 'page' => 1, 'category' => $settingCategory
            ));
        }

        $setting = GeolocationSettings::where(['key' => 'places_application_id'])->first();
        if(!$setting){
            GeolocationSettings::updateOrCreate(array('key' => 'places_application_id'), array(
                'value' => '', 'page' => 1, 'category' => $settingCategory
            ));
        }
        
        // Redundancy
        $setting = GeolocationSettings::where(['key' => 'places_redundancy_rule'])->first();
        if(!$setting){
            GeolocationSettings::updateOrCreate(array('key' => 'places_redundancy_rule'), array(
                'value' => '0', 'page' => 1, 'category' => $settingCategory
            ));
        }
        $setting = GeolocationSettings::where(['key' => 'places_provider_redundancy'])->first();
        if(!$setting){
            GeolocationSettings::updateOrCreate(array('key' => 'places_provider_redundancy'), array(
                'value' => 'google_maps', 'page' => 1, 'category' => $settingCategory
            ));
        }
        $setting = GeolocationSettings::where(['key' => 'places_key_redundancy'])->first();
        if(!$setting){
            GeolocationSettings::updateOrCreate(array('key' => 'places_key_redundancy'), array(
                'value' => '', 'page' => 1, 'category' => $settingCategory
            ));
        }
        $setting = GeolocationSettings::where(['key' => 'places_url_redundancy'])->first();
        if(!$setting){
            GeolocationSettings::updateOrCreate(array('key' => 'places_url_redundancy'), array(
                'value' => '', 'page' => 1, 'category' => $settingCategory
            ));
        }
        $setting = GeolocationSettings::where(['key' => 'places_application_id_redundancy'])->first();
        if(!$setting){
            GeolocationSettings::updateOrCreate(array('key' => 'places_application_id_redundancy'), array(
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
