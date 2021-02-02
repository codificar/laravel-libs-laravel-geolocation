<?php
namespace Codificar\Geolocation\Enums;

class DirectionsEnum {
	/*
		|--------------------------------------------------------------------------
		| Enums of Geolocation Directions
		|--------------------------------------------------------------------------
	*/
    const DirectionsProvider = array(
	    array('value' => 'google_maps', 	'name' => 'Google Maps', 'redundancy_url' => false)
	   ,array('value' => 'bing_maps', 		'name' => 'Bing Maps', 'redundancy_url' => false)
	   ,array('value' => 'mapquest_maps', 	'name' => 'MapQuest Maps', 'redundancy_url' => false)
	   ,array('value' => 'mapbox_maps', 	'name' => 'MapBox Maps', 'redundancy_url' => false)
	   ,array('value' => 'openroute_maps', 	'name' => 'OpenRouteService Maps', 'redundancy_url' => true)
	   ,array('value' => 'flight_map', 'name' => 'Flight Maps', 'redundancy_url' => false)
	   ,array('value' => 'here_maps', 'name' => 'Here Maps', 'redundancy_url' => false)
	);

}