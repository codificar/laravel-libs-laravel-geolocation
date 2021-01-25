<?php
namespace Codificar\Geolocation\Enums;

class MapsEnum {
	/*
		|--------------------------------------------------------------------------
		| Enums of Geolocation Maps
		|--------------------------------------------------------------------------
    */
    const MapsProvider = array(
		 array('value' => 'osm', 	'name' => 'Open Street Maps', 'redundancy_url' => false, 'redundancy_id' => false, 'required_key' => false)
		,array('value' => 'google', 	'name' => 'Google Maps', 'redundancy_url' => false, 'redundancy_id' => false, 'required_key' => true)
	);

}