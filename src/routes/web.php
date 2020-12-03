<?php

//Admin APIs
Route::group(['prefix' => '/api/v1/libs/geolocation', 'namespace' => 'Codificar\Geolocation\Http\Controllers', 'middleware' => ['auth.admin_api', 'cors']], function () { 

    Route::get('/admin/get_address_string', ['as' => 'adminAutocompleteUrl', 'uses' => 'GeolocationController@getAddressByString']);
    Route::get('/admin/geocode', ['as' => 'adminGeocodeUrl', 'uses' => 'GeolocationController@geocode']);
    
    Route::get('/admin/geocode_reverse', ['as' => 'adminGeocodeUrlGeolocationLib', 'uses' => 'GeolocationController@geocodeReverse']);
    Route::get('/admin/get_place_details', 'GeolocationController@getDetailsById');

    //Directions API
    Route::get('/admin/geocode/get_estimate', 'DirectionsController@getDirectionsDistanceAndTimeApi');
    Route::get('/admin/geocode/get_polyline_and_estimate', ['as' => 'adminPolylineByGeocode', 'uses' => 'DirectionsController@getPolylineAndEstimateByDirectionsApi']);
    Route::get('/admin/address/get_polyline_and_estimate', ['as' => 'adminPolylineByAddress', 'uses' => 'DirectionsController@getPolylineAndEstimateByAddressesApi']);
    //Only Motoboys
    Route::get('admin/get_polyline_waypoints', array('as' => 'adminPolylineWithPoints', 'uses' => 'DirectionsController@getPolylineAndEstimateWithWayPointsApi'));   
});

//Corp APIs
Route::group(['prefix' => '/api/v1/libs/geolocation', 'namespace' => 'Codificar\Geolocation\Http\Controllers', 'middleware' => ['auth.corp_api', 'cors']], function () {  
    Route::get('/corp/get_address_string', ['as' => 'corpAutocompleteUrl', 'uses' => 'GeolocationController@getAddressByString']);
    Route::get('/corp/geocode', ['as' => 'corpGeocodeUrl', 'uses' => 'GeolocationController@geocode']);

    //Only Motoboys
    Route::get('corp/get_polyline_waypoints', array('as' => 'corpPolylineWithPoints', 'uses' => 'DirectionsController@getPolylineAndEstimateWithWayPointsApi'));  
});

//User Painel APIs
Route::group(['prefix' => '/api/v1/libs/geolocation', 'namespace' => 'Codificar\Geolocation\Http\Controllers', 'middleware' => ['auth.user_api', 'cors']], function () {  
    Route::get('/user/get_address_string', ['as' => 'userAutocompleteUrl', 'uses' => 'GeolocationController@getAddressByString']);
    Route::get('/user/geocode', ['as' => 'userGeocodeUrl', 'uses' => 'GeolocationController@geocode']);

    //Only Motoboys
    Route::get('/user/get_polyline_waypoints', array('as' => 'userPolylineWithPoints', 'uses' => 'DirectionsController@getPolylineAndEstimateWithWayPointsApi')); 
});

//User APP APIs Uberclone
Route::group(['prefix' => '/user', 'namespace' => 'Codificar\Geolocation\Http\Controllers', 'middleware' => 'auth.user_api:api' ], function () {  
    //PLACES
    //Auto complete
    Route::get('/get_address_string', 'GeolocationController@getAddressByString');
    //Get Geocode By PlaceId
    Route::post('/get_place_details', 'GeolocationController@geocodeByPlaceId');
    //Get Geocode Reverse
    Route::post('/geolocation/get_address_by_geocode', 'GeolocationController@geocodeReverse');
    //Get Geocode
    Route::post('/geolocation/get_geocode_by_address', 'GeolocationController@geocode');   

    //DIRECTIONS
    Route::post('/geolocation/get_polyline', 'DirectionsController@getPolylineAndEstimateByDirections');   
    Route::post('/geolocation/get_distance_time', 'DirectionsController@getDistanceAndTimeByDirections');   
    Route::post('/get_polyline_and_estimate', 'DirectionsController@getPolylineAndEstimateByAddresses');  
});

//User APP APIs Motoboys
Route::group(['prefix' => '/api/v1/user', 'namespace' => 'Codificar\Geolocation\Http\Controllers', 'middleware' => 'auth.user_api:api' ], function () {  
    //PLACES

    //Places AutoComplete
    Route::get('/get_address_string', 'GeolocationController@getAddressByString');
    //Get Reverse Geocode
    Route::post('/get_address_from_lat_long', 'GeolocationController@geocodeReverse');  
    //Get Geocode
    Route::post('/get_lat_long_from_address', 'GeolocationController@geocode');  
    //Get Geocode By PlaceId
    Route::post('/get_place_details', 'GeolocationController@geocodeByPlaceId');
});

//Provider APP APIs
Route::group(['prefix' => '/provider', 'namespace' => 'Codificar\Geolocation\Http\Controllers', 'middleware' => 'auth.provider_api:api' ], function () {  
    //PLACES   
    //Auto complete
    Route::get('/get_address_string', 'GeolocationController@getAddressByString');
    
    //Get Geocode Reverse
    // Route::post('/getAddressFromLatLong', 'GeolocationController@geocodeReverse');
    //Get Geocode
    // Route::post('/getLatLngFromAddress', 'GeolocationController@geocode');   

    //DIRECTIONS
    // Route::post('/get_polyline', 'DirectionsController@getPolylineAndEstimateByDirections');      
});

//Admin Painel Routes
Route::group(['prefix' => '/admin/libs/geolocation', 'namespace' => 'Codificar\Geolocation\Http\Controllers'], function(){
    //Settings
    Route::group(['prefix' => '/settings'], function () {  
        Route::get('/', array('as' => 'adminGeolocationSetting', 'uses' => 'GeolocationSettingsController@create'));
        Route::post('/', array('as' => 'adminGeolocationSettingSave', 'uses' => 'GeolocationSettingsController@store'));      
    });
});

/**
 * Rota para permitir utilizar arquivos de traducao do laravel (dessa lib) no vue js
 */
Route::get('/libs/geolocation/lang.trans/{file}', function () {
    $fileNames = explode(',', Request::segment(4));
    $lang = config('app.locale');
    $files = array();
    foreach ($fileNames as $fileName) {
        array_push($files, __DIR__.'/../resources/lang/' . $lang . '/' . $fileName . '.php');
    }
    $strings = [];
    foreach ($files as $file) {
        $name = basename($file, '.php');
        $strings[$name] = require $file;
    }

    header('Content-Type: text/javascript');
    return ('window.lang = ' . json_encode($strings) . ';');
    exit();
})->name('assets.lang');
