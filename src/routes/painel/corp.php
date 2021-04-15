<?php
Route::group(['prefix' => '/api/v1/libs/geolocation', 'namespace' => 'Codificar\Geolocation\Http\Controllers', 'middleware' => ['auth.corp_api', 'cors']], function () {  
    Route::get('/corp/get_address_string', ['as' => 'corpAutocompleteUrl', 'uses' => 'GeolocationController@getAddressByString']);
    Route::get('/corp/geocode', ['as' => 'corpGeocodeUrl', 'uses' => 'GeolocationController@geocode']);
    Route::get('/corp/geocode_reverse', ['as' => 'corpGeocodeUrlGeolocationLib', 'uses' => 'GeolocationController@geocodeReverse']);
    //Only Motoboys
    Route::get('corp/get_polyline_waypoints', array('as' => 'corpPolylineWithPoints', 'uses' => 'DirectionsController@getPolylineAndEstimateWithWayPointsApi'));  

    //Only Fretes
    Route::get('/corp/geocode/get_polyline_and_estimate', ['as' => 'corpPolylineByGeocode', 'uses' => 'DirectionsController@getPolylineAndEstimateByDirectionsApi']);
});