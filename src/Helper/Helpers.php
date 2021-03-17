<?php
namespace Codificar\Geocode\Helper;

function convert_distance_format($unit_dist, $response_dist){
	if (isset($response_dist)) {
		$dist = ($response_dist / 1000);
	} else {
		$dist = 0;
	}

	return $dist;
}

function convert_to_minutes($response_time){
	if (isset($response_time))
		$time_in_Minutes = ($response_time / 60);
	else
		$time_in_Minutes = 0;

	return $time_in_Minutes;
}

function convert_to_miliseconds_to_minutes($response_time){
	if (isset($response_time))
		$time_in_Minutes = ($response_time / 60000);
	else
		$time_in_Minutes = 0;

	return $time_in_Minutes;
}