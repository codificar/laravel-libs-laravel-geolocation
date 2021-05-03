<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMatrixDistanceSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Settings::where('key', 'directions_matrix_distance')->first()) {
            Settings::create([
                "key" => "directions_matrix_distance",
                "value" => '0',
                "page" => '1',
                "category" => "9",
                "sub_category" => "0"
            ]);
        }

        if (!Settings::where('key', 'directions_matrix_distance_redundancy')->first()) {
            Settings::create([
                "key" => "directions_matrix_distance_redundancy",
                "value" => '0',
                "page" => '1',
                "category" => "9",
                "sub_category" => "0"
            ]);
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
}
