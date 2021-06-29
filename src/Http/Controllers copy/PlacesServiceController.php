<?php

namespace Codificar\Geolocation\Http\Controllers;

//Laravel Uses
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use View;

//Internal Uses
use Codificar\Geolocation\Http\Requests\PlacesFormRequest;
use Codificar\Geolocation\Http\Requests\GeocodeFormRequest;
use Codificar\Geolocation\Http\Requests\GeocodeReverseFormRequest;
use Codificar\Geolocation\Http\Requests\PlaceDetailsFormRequest;
use Codificar\Geolocation\Http\Requests\GeocodePlaceIdFormRequest;

use Codificar\Geolocation\Http\Resources\PlacesResource;
use Codificar\Geolocation\Http\Resources\GeocodeResource;
use Codificar\Geolocation\Http\Resources\PlaceDetailsResource;

// use MapsFactory, Settings;
use Codificar\Geolocation\Lib\MapsFactory;
use Codificar\Geolocation\Models\GeolocationSettings;

class PlacesServiceController extends Controller {  
    public function places($place, $latitude, $longitude, $clicker = 'primary')  {
        $placesClicker = $clicker == "redundancy" ? "redundancy_places" : "places";
        $factory = new MapsFactory($placesClicker);
        
        if($factory) {
            $clicker = $factory->createMaps();
            $response = $clicker->getAddressByTextWithLatLng($place,$latitude,$longitude);
        }else{
            $response = array("success" => false, "data" => [], "error_message" => trans('geolocationTrans::geolocation.no_data_found'));
        }

        $response['clicker'] = "primary";

        if((!$factory || $response['success'] == false) && GeolocationSettings::getPlacesRedundancyRule()) {
            $factoryRedundancy = new MapsFactory('redundancy_places');

            if($factoryRedundancy){
                $clickerRedundancy = $factoryRedundancy->createMaps();
                $response = $clickerRedundancy->getAddressByTextWithLatLng($place,$latitude,$longitude);
            }

            $response['clicker'] = "redundancy";
        }

        return $response;
    }

    public function geocode($address, $place_id = null, $clicker = 'primary') {
        $placesClicker = $clicker == "redundancy" ? "redundancy_places" : "places";
        $factory = new MapsFactory($placesClicker);
        
        if($factory) {
            $clicker = $factory->createMaps();
            $response = $clicker->getGeocodeWithAddress($address, $place_id, null, null, null);
        }
        else {
            $response = array("success" => false, "data" => [], "error_message" => trans('geolocationTrans::geolocation.no_data_found'));
        }
        
        $response['clicker'] = "primary";

        if((!$factory || $response['success'] == false) && GeolocationSettings::getPlacesRedundancyRule()) {
            $factoryRedundancy = new MapsFactory('redundancy_places');

            if($factoryRedundancy){
                $clickerRedundancy = $factoryRedundancy->createMaps();
                $response = $clickerRedundancy->getGeocodeWithAddress($address, $place_id, null, null, null);
            }

            $response['clicker'] = "redundancy";
        }

        if($response['success']) $response['data']['address'] = $address;

        return $response;
    }

    public function geocodeReverse($latitude, $longitude, $clicker = 'primary') {
        $placesClicker = $clicker == "redundancy" ? "redundancy_places" : "places";
        $factory = new MapsFactory($placesClicker);

        if($factory)
        {
            $clicker = $factory->createMaps();
            $response = $clicker->getGeocodeByLatLng($latitude, $longitude);
        }
        else
        {
            $response = array("success" => false, "data" => [], "error_message" => trans('geolocationTrans::geolocation.no_data_found'));
        }

        $response['clicker'] = "primary";
        if((!$factory || $response['success'] == false) && GeolocationSettings::getPlacesRedundancyRule()) {
            $factoryRedundancy = new MapsFactory('redundancy_places');

            if($factoryRedundancy){
                $clickerRedundancy = $factoryRedundancy->createMaps();
                $response = $clickerRedundancy->getGeocodeByLatLng($latitude, $longitude);
            }

            $response['clicker'] = "redundancy";
        }

        return $response;
    }
    
    public function findByPlaceId($place_id) {
        if(GeolocationSettings::getPlacesProvider() == "google_maps"){
            $factory = new MapsFactory('places');

            if($factory){
                $clicker = $factory->createMaps();
                $response = $clicker->getDetailsById($place_id);
            }else{
                $response = array("success" => false, "data" => [], "error_message" => trans('geolocationTrans::geolocation.no_data_found'));
            }
        }else{
            $response = array("success" => false, "data" => [], "error_message" => trans('geolocationTrans::geolocation.no_lib_google'));
        }
		
        return $response;
    }
    
}
