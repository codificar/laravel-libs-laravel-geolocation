<?php
namespace Codificar\Geolocation\Enums;

class MatrixDistanceEnum {
	/*
		|--------------------------------------------------------------------------
		| Enums of Geolocation Directions
		|--------------------------------------------------------------------------
	*/
    const MatrixDistance = array(
	    array('name' => 'google_maps', 'value' => true)
	   ,array('name' => 'bing_maps', 'value' => true)
	   ,array('name' => 'mapquest_maps', 'value' => true)
	   ,array('name' => 'mapbox_maps', 'value' => true)
	   ,array('name' => 'openroute_maps', 'value' => true)
	   ,array('name' => 'flight_map', 'value' => true)
	   ,array('name' => 'here_maps', 'value' => true)
	);

}