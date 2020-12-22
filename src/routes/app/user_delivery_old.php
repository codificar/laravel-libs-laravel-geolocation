<?php
Route::group(['prefix' => '/api/v1', 'namespace' => 'Codificar\Geolocation\Http\Controllers\api' ], function () {
    //Get Reverse Geocode
    Route::post('/user/get_address_from_lat_long', 'GeolocationControllerV1@geocodeReverse');  

    Route::get('/geolocation/get_address_string', 'GeolocationControllerV1@getAddressByString');  
});
