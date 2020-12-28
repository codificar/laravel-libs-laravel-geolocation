<?php
Route::group(['prefix' => '/user', 'namespace' => 'Codificar\Geolocation\Http\Controllers', 'middleware' => 'auth.user_api:api' ], function () {  
    //Auto complete
    Route::get('/get_address_string', 'GeolocationController@getAddressByString');
    //Get Geocode By PlaceId
    Route::post('/get_place_details', 'GeolocationController@geocodeByPlaceId');
    //Get Geocode Reverse
    Route::post('/geolocation/get_address_by_geocode', 'GeolocationController@geocodeReverse');
    //Get Geocode
    Route::post('/geolocation/get_geocode_by_address', 'GeolocationController@geocode');   
});

//V1 Motoboy Prefix
Route::group(['prefix' => '/api/v1/user', 'namespace' => 'Codificar\Geolocation\Http\Controllers', 'middleware' => 'auth.user_api:api' ], function () {  
    Route::post('/geolocation/get_address_from_lat_long', 'GeolocationController@geocodeReverse');  
  
    
    //Auto complete
    Route::get('/get_address_string', 'GeolocationController@getAddressByString');
    //Get Geocode By PlaceId
    Route::post('/get_place_details', 'GeolocationController@geocodeByPlaceId');
    //Get Geocode Reverse
    Route::post('/geolocation/get_address_by_geocode', 'GeolocationController@geocodeReverse');
    //Get Geocode
    Route::post('/geolocation/get_geocode_by_address', 'GeolocationController@geocode');   
});

