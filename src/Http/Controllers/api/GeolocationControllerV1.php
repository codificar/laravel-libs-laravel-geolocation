<?php

namespace Codificar\Geolocation\Http\Controllers\api;

//Laravel Uses
use Codificar\Geolocation\Http\Requests\GetLatLngByAddressFormRequest;
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

class GeolocationControllerV1 extends Controller {  
   
    public function geocodeByPlaceId(Request $request) {
        $placesClicker = $request->clicker == "redundancy" ? "redundancy_places" : "places";
        $this->factory = new MapsFactory($placesClicker);
        
        if($this->factory) {
            $this->clicker = $this->factory->createMaps();
            $data = $this->clicker->getGeocodeWithAddress($request->address, $request->placeID, $request->lang);
        }
        else {
            $response = array("success" => false, "data" => [], "error_message" => trans('geolocationTrans::geolocation.no_data_found'));
        }
      
        $response['clicker'] = "primary";

        if((!$this->factory || $data['success'] == false) && GeolocationSettings::getPlacesRedundancyRule()) {
            $this->factoryRedundancy = new MapsFactory('redundancy_places');

            if($this->factoryRedundancy){
                $this->clickerRedundancy = $this->factoryRedundancy->createMaps();
                $data = $this->clickerRedundancy->getGeocodeWithAddress($request->address, $request->placeID, $request->lang);
            }

            $response['clicker'] = "redundancy";
        }

        if($data['success']){
            $response['success'] = true;
            $response['address'] = $data['data']['address'];
            $response['latitude'] = $data['data']['latitude'];
            $response['longitude'] = $data['data']['longitude'];
        }else {
            $response = array("success" => false, "data" => [], "error_message" => trans('geolocationTrans::geolocation.no_data_found'));
        }
       

        return $response;
    }   

    public function geocodeReverse(Request $request) {
        $placesClicker = $request->clicker == "redundancy" ? "redundancy_places" : "places";
        $this->factory = new MapsFactory($placesClicker);

        if($this->factory)
        {
            $this->clicker = $this->factory->createMaps();
            $data = $this->clicker->getGeocodeByLatLng($request->latitude, $request->longitude);
        }
        else
        {
            $response = array("success" => false, "data" => [], "error_message" => trans('geolocationTrans::geolocation.no_data_found'));
        }

        $response['clicker'] = "primary";
        if((!$this->factory || $data['success'] == false) && GeolocationSettings::getPlacesRedundancyRule()) {
            $this->factoryRedundancy = new MapsFactory('redundancy_places');

            if($this->factoryRedundancy){
                $this->clickerRedundancy = $this->factoryRedundancy->createMaps();
                $data = $this->clickerRedundancy->getGeocodeByLatLng($request->latitude, $request->longitude);
            }

            $response['clicker'] = "redundancy";
        }

        if($data['success']){
            $response['success'] = true;
            $response['address'] = $data['data']['address'];
            $response['latitude'] = $data['data']['latitude'];
            $response['longitude'] = $data['data']['longitude'];
        }else {
            $response = array("success" => false, "data" => [], "error_message" => trans('geolocationTrans::geolocation.no_data_found'));
        }

        return $response;
    }

    /**
     * @return array lat and lng corrresponding to the address.
     * @api{get}/admin/get_lat_lang_by_address Get route between two addresses
     * @apiDescription Function returns the route and info between two address
     */
    public function GetLatLangByAddress(GetLatLngByAddressFormRequest $request)
    {
        $factory = new MapsFactory(MapsFactory::TYPE_GEOCODING);
        if ($factory) {
            $clicker = $factory->createMaps();
            $response = $clicker->getLatLangFromAddress($request['address']);
        } else {
            $response = [
                "success" => false,
                "data" => [],
                "error_message" => trans('maps_lib.no_data_found')
            ];
        }
        return $response;
    }


    public function getDirectionsDistanceAndTime(Request $request) {  
        $startLat = $request->latitude;
        $startLng = $request->longitude;
        $destLat = $request->dest_latitude;
        $destLng = $request->dest_longitude;

        $factory = new MapsFactory('directions');
        $clicker = $factory->createMaps();

        if($clicker){
            $requestTimeDistance = $clicker->getDistanceAndTimeByDirections(
                $startLat, $startLng, $destLat, $destLng
            );
        }
        
        if((!isset($requestTimeDistance['success']) || !$requestTimeDistance['success']) && GeolocationSettings::getDirectionsRedundancyRule()){
            $factoryRedundancy = new MapsFactory('redundancy_directions');

            if($factoryRedundancy){
                $clickerRedundancy = $factoryRedundancy->createMaps();

                if($clickerRedundancy)
                    $requestTimeDistance = $clickerRedundancy->getDistanceAndTimeByDirections(
                        $startLat, $startLng, $destLat, $destLng
                    );
            }
        }

        if($requestTimeDistance['success']){
            $response['success'] = true;
            $response['distance_string'] = $requestTimeDistance['data']['distance_text'];
            $response['time_string'] = $requestTimeDistance['data']['duration_text'];
            $response['distance_float'] = $requestTimeDistance['data']['distance'];
            $response['time_float'] = $requestTimeDistance['data']['time_in_minutes'];
        }else {
            $response = array("success" => false, "data" => [], "error_message" => trans('geolocationTrans::geolocation.no_data_found'));
        }


        return $response;
    }

    public function geocode(GeocodeFormRequest $request) {
        $placesClicker = $request->clicker == "redundancy" ? "redundancy_places" : "places";
        $this->factory = new MapsFactory($placesClicker);
        
        if($this->factory) {
            $this->clicker = $this->factory->createMaps();
            $response = $this->clicker->getGeocodeWithAddress($request->address, $request->place_id, $request->lang, $request->latitude, $request->longitude);
        }
        else {
            $response = array("success" => false, "data" => [], "error_message" => trans('geolocationTrans::geolocation.no_data_found'));
        }
        
        $response['clicker'] = "primary";
        if((!$this->factory || $response['success'] == false) && GeolocationSettings::getPlacesRedundancyRule()) {
            $this->factoryRedundancy = new MapsFactory('redundancy_places');

            if($this->factoryRedundancy){
                $this->clickerRedundancy = $this->factoryRedundancy->createMaps();
                $response = $this->clickerRedundancy->getGeocodeWithAddress($request->address, $request->place_id, $request->lang, null, null);
            }

            $response['clicker'] = "redundancy";
        }

        if($response['success']) $response['data']['address'] = $request->address;

        return new GeocodeResource(["response" => $response]);
    } 

    public function getAddressByString(PlacesFormRequest $request)  {
        $this->factory = new MapsFactory('places');
        
        if($this->factory) {
            $this->clicker = $this->factory->createMaps();
            $response = $this->clicker->getAddressByTextWithLatLng($request->place,$request->latitude,$request->longitude);
        }else{
            $response = array("success" => false, "data" => [], "error_message" => trans('geolocationTrans::geolocation.no_data_found'));
        }

        $response['clicker'] = "primary";

        if((!$this->factory || $response['success'] == false) && GeolocationSettings::getPlacesRedundancyRule()) {
            $this->factoryRedundancy = new MapsFactory('redundancy_places');

            if($this->factoryRedundancy){
                $this->clickerRedundancy = $this->factoryRedundancy->createMaps();
                $response = $this->clickerRedundancy->getAddressByTextWithLatLng($request->place,$request->latitude,$request->longitude);
            }

            $response['clicker'] = "redundancy";
        }

        return new PlacesResource(["response" => $response]);
    }
   
    
}
