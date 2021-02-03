<?php
namespace Codificar\Geolocation\Enums;

class PlacesEnum {
	/*
		|--------------------------------------------------------------------------
		| Enums of Geolocation Places
		|--------------------------------------------------------------------------
    */
    const PlacesProvider = array(
		 array('value' => 'pelias_maps', 	'name' => 'Pelias Maps', 'redundancy_url' => true, 'redundancy_id' => true)
		,array('value' => 'google_maps', 	'name' => 'Google Maps', 'redundancy_url' => false, 'redundancy_id' => false)
		,array('value' => 'algolia_maps', 	'name' => 'Algolia Maps', 'redundancy_url' => false, 'redundancy_id' => true)
		,array('value' => 'here_maps', 		'name' => 'Here Maps', 'redundancy_url' => false, 'redundancy_id' => false)	
		,array('value' => 'flight_map', 'name' 	   => 'Flight Maps', 'redundancy_url' => false, 'redundancy_id' => false)
		,array('value' => 'bing_maps', 'name' 	   => 'Bing Maps', 'redundancy_url' => false, 'redundancy_id' => false)
		,array('value' => 'openroute_maps', 	'name' => 'OpenRouteService Maps', 'redundancy_url' => false)
	);

}