<?php
// Route::group(['prefix' => '/provider', 'namespace' => 'Codificar\Geolocation\Http\Controllers', 'middleware' => 'auth.provider_api:api' ], function () {  
Route::group(['prefix' => '/provider', 'namespace' => 'Codificar\Geolocation\Http\Controllers'], function () {  
    //PLACES   
    Route::get('/get_address_string', 'GeolocationController@getAddressByString');

    //Only Services
    Route::post('/geolocation/get_polyline', 'DirectionsController@getPolylineAndEstimateByDirectionsApi');       

    //Get Geocode By PlaceId
    Route::post('/get_place_details', 'GeolocationController@geocodeByPlaceId');
    //Get Geocode Reverse
    Route::post('/geolocation/get_address_by_geocode', 'GeolocationController@geocodeReverse');
    //Get Geocode
    Route::post('/geolocation/get_geocode_by_address', 'GeolocationController@geocode');   
});

//Only Fretes
Route::group(['prefix' => '/api/v1/provider', 'namespace' => 'Codificar\Geolocation\Http\Controllers', 'middleware' => 'auth.provider_api:api' ], function () {  
//Direction       
    Route::get('/geolocation/get_polyline', 'DirectionsController@getAddressByString'); 

    //Get Geocode By PlaceId
    Route::post('/get_place_details', 'GeolocationController@geocodeByPlaceId');
    //Get Geocode Reverse
    Route::post('/geolocation/get_address_by_geocode', 'GeolocationController@geocodeReverse');
    //Get Geocode
    Route::post('/geolocation/get_geocode_by_address', 'GeolocationController@geocode');  
});
 