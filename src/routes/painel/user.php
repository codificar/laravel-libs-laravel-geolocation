<?php
//User Painel APIs
Route::group(['prefix' => '/api/v1/libs/geolocation', 'namespace' => 'Codificar\Geolocation\Http\Controllers', 'middleware' => ['auth.user_api', 'cors']], function () {  
    Route::get('/user/get_address_string', ['as' => 'userAutocompleteUrl', 'uses' => 'GeolocationController@getAddressByString']);
    Route::get('/user/geocode', ['as' => 'userGeocodeUrl', 'uses' => 'GeolocationController@geocode']);
    Route::get('/user/geocode_reverse', ['as' => 'userGeocodeUrlGeolocationLib', 'uses' => 'GeolocationController@geocodeReverse']);

    //Only Motoboys
    Route::get('/user/get_polyline_waypoints', array('as' => 'userPolylineWithPoints', 'uses' => 'DirectionsController@getPolylineAndEstimateWithWayPointsApi')); 
});