<?php
//Admin APIs
Route::group(['prefix' => '/api/v1/libs/geolocation', 'namespace' => 'Codificar\Geolocation\Http\Controllers', 'middleware' => ['auth.admin_api', 'cors']], function () {  
    //Places and Geocode
    Route::get('/', ['as' => 'inputTeste', 'uses' => 'GeolocationController@index']);
    Route::get('/admin/get_address_string', ['as' => 'adminAutocompleteUrlGeolocationLib', 'uses' => 'GeolocationController@getAddressByString']);
    Route::get('/admin/geocode', ['as' => 'adminGeocodeUrlGeolocationLib', 'uses' => 'GeolocationController@geocode']);
    Route::get('/admin/geocode_reverse', ['as' => 'adminGeocodeUrlGeolocationLib', 'uses' => 'GeolocationController@geocodeReverse']);
    Route::get('/admin/get_place_details', 'GeolocationController@getDetailsById');

    Route::post('/admin/directions', 'DirectionsController@getDirectionsDistanceAndTime');

   
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
