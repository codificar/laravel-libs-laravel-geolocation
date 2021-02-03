<?php
//APIs
Route::group(['prefix' => '/api/v1/libs/geolocation/public', 'namespace' => 'Codificar\Geolocation\Http\Controllers', 'middleware' => ['cors']], function () { 

    Route::get('/get_address_string', ['as' => 'publicAutocompleteUrl', 'uses' => 'GeolocationController@getAddressByString']);
    Route::get('/geocode', ['as' => 'publicGeocodeUrl', 'uses' => 'GeolocationController@geocode']);
    
    Route::get('/geocode_reverse', ['as' => 'publicGeocodeUrlGeolocationLib', 'uses' => 'GeolocationController@geocodeReverse']);
    Route::get('/get_place_details', 'GeolocationController@getDetailsById');

    //Directions API
    Route::get('/geocode/get_estimate', 'DirectionsController@getDirectionsDistanceAndTimeApi');
    Route::get('/geocode/get_polyline_and_estimate', ['as' => 'publicPolylineByGeocode', 'uses' => 'DirectionsController@getPolylineAndEstimateByDirectionsApi']);
    Route::get('/address/get_polyline_and_estimate', ['as' => 'publicPolylineByAddress', 'uses' => 'DirectionsController@getPolylineAndEstimateByAddressesApi']);
    
    //Only Motoboys
    Route::get('/get_polyline_waypoints', array('as' => 'publicPolylineWithPoints', 'uses' => 'DirectionsController@getPolylineAndEstimateWithWayPointsApi'));   
});