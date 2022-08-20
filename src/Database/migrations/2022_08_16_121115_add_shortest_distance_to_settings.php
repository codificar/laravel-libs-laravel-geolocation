<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShortestDistanceToSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Settings::updateOrCreate(array('key' => 'directions_shortest_distance',
            'value'         => '0',
            'tool_tip'      => 'enabe short distance when planning ride route',
            'page'          => '1',
            'category'      => \Codificar\Geolocation\Models\GeolocationSettings::getGeolocationCategory(),
            'sub_category'  => '0',
        ));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $sessionTokenWorkConfig = Settings::findObjectByKey('directions_shortest_distance');

        if($sessionTokenWorkConfig)
            $sessionTokenWorkConfig->delete();
    }
}
