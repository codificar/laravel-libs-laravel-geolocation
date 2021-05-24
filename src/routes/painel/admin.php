<?php
//APIs
Route::group(['prefix' => '/api/v1/libs/geolocation', 'namespace' => 'Codificar\Geolocation\Http\Controllers', 'middleware' => ['auth.admin_api', 'cors']], function () { 

    Route::get('/admin/get_address_string', ['as' => 'adminAutocompleteUrl', 'uses' => 'GeolocationController@getAddressByString']);
    Route::get('/admin/geocode', ['as' => 'adminGeocodeUrl', 'uses' => 'GeolocationController@geocode']);
    
    Route::get('/admin/geocode_reverse', ['as' => 'adminGeocodeUrlGeolocationLib', 'uses' => 'GeolocationController@geocodeReverse']);

    Route::get('/admin/get_place_details', ['as' => 'adminGeocodeGetPlaceDetail', 'uses' => 'GeolocationController@getDetailsById']);

    //Directions API
    Route::get('/admin/geocode/get_estimate', 'DirectionsController@getDirectionsDistanceAndTimeApi');
    Route::get('/admin/geocode/get_polyline_and_estimate', ['as' => 'adminPolylineByGeocode', 'uses' => 'DirectionsController@getPolylineAndEstimateByDirectionsApi']);
    Route::get('/admin/address/get_polyline_and_estimate', ['as' => 'adminPolylineByAddress', 'uses' => 'DirectionsController@getPolylineAndEstimateByAddressesApi']);
    
    //Only Motoboys
    Route::get('admin/get_polyline_waypoints', array('as' => 'adminPolylineWithPoints', 'uses' => 'DirectionsController@getPolylineAndEstimateWithWayPointsApi'));   
});