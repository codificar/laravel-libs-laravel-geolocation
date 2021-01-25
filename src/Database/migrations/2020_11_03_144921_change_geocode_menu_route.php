<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeGeocodeMenuRoute extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $permission = Permission::updateOrCreate(
            ['name' => 'Geolocation'],
            [
			'name' => 'Geolocation',
			'parent_id' => 2319,
			'order' => 913,
			'is_menu' => 1,
			'url' => '/admin/libs/geolocation/settings',
			'icon' => 'fa fa-globe'
            ]
        );
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
