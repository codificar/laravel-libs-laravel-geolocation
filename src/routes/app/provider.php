<?php
Route::group(['prefix' => '/provider', 'namespace' => 'Codificar\Geolocation\Http\Controllers', 'middleware' => 'auth.provider_api:api' ], function () {  
    //PLACES   
    Route::get('/get_address_string', 'GeolocationController@getAddressByString');      
});

//Only Fretes
Route::group(['prefix' => '/api/v1/provider', 'namespace' => 'Codificar\Geolocation\Http\Controllers', 'middleware' => 'auth.provider_api:api' ], function () {  
//Direction       
    Route::get('/geolocation/get_polyline', 'DirectionsController@getAddressByString'); 
});
 