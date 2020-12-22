<?php
//Admin Painel Routes
Route::group(['prefix' => '/admin/libs/geolocation', 'namespace' => 'Codificar\Geolocation\Http\Controllers', 'middleware' => 'auth.admin'], function(){
    Route::group(['prefix' => '/settings'], function () {  
        Route::get('/', array('as' => 'adminGeolocationSetting', 'uses' => 'GeolocationSettingsController@create'));
        Route::post('/', array('as' => 'adminGeolocationSettingSave', 'uses' => 'GeolocationSettingsController@store'));      
    });
});