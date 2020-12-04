<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

//Internal Uses
use Codificar\Geolocation\Models\GeolocationSettings;

class AddMapsProviderSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $settingCategory = GeolocationSettings::getGeolocationCategory();
        Settings::updateOrCreate(array('key' => 'maps_provider'), array('value' => 'osm', 'page' => 1, 'category' => $settingCategory));
        Settings::updateOrCreate(array('key' => 'maps_key'), array('value' => '', 'page' => 1, 'category' => $settingCategory));

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
