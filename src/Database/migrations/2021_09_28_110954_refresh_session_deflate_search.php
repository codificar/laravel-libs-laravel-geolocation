<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RefreshSessionDeflateSearch extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Settings::updateOrCreate(array('key' => 'refresh_session_deflate_search',
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
        $refreshConfig = Settings::findObjectByKey('refresh_session_deflate_search');

        if($refreshConfig)
            $refreshConfig->delete();
    }
}
