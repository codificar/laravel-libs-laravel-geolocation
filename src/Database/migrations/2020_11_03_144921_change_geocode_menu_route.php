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
        $permission = Permission::where('name', 'Geolocation')->where('url', '/admin/settings/geolocation')
        ->update(['url' => '/admin/libs/geolocation/settings']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $permission = Permission::where('name', 'Geolocation')->where('url', '/admin/libs/geolocation/settings')
        ->update(['url' => '/admin/settings/geolocation']);
    }
}
