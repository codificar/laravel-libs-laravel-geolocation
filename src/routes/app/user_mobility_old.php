<?php
Route::group(['prefix' => '/user', 'namespace' => 'Codificar\Geolocation\Http\Controllers\api', 'middleware' => 'auth.user_api:api' ], function () {  
    Route::post('/getAddressFromPlaceId', 'GeolocationControllerV1@geocodeByPlaceId');
    Route::post('/getAddressFromLatLong', 'GeolocationControllerV1@geocodeReverse');
    Route::get('/get_distance_time', 'GeolocationControllerV1@getDirectionsDistanceAndTime');
    Route::post('/getLatLngFromAddress', 'GeolocationControllerV1@geocode');
});