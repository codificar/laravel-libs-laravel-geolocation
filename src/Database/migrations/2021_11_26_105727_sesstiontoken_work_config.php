<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SesstiontokenWorkConfig extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Settings::updateOrCreate(array('key' => 'sessiontoken_work',
            'value'         => '0', 
            'tool_tip'      => '', 
            'page'          => '1',
            'category'      => '9',
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
        $refreshConfig = Settings::findObjectByKey('sessiontoken_work');

        if($refreshConfig)
            $refreshConfig->delete();
    }
}
