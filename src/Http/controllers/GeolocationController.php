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

class GeolocationController extends Controller {  
    public function index(){
        $enviroment = 'admin';
		return View::make('geolocation::addressAutocomplete.index')
		->with('enviroment', $enviroment);
    }

    public function teste(){
        $enviroment = 'admin';
		return View::make('geolocation::addressAutocomplete.index')
		->with('enviroment', $enviroment);
    }
    
    public function getAddressByString(PlacesFormRequest $request)  {
        $this->factory = new MapsFactory('places');
        
        if($this->factory) {
            $this->clicker = $this->factory->createMaps();
            $response = $this->clicker->getAddressByTextWithLatLng($request->place,$request->latitude,$request->longitude);
        }else{
            $response = array("success" => false, "data" => [], "error_message" => trans('maps_lib.no_data_found'));
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

    public function geocodeByPlaceId(GeocodePlaceIdFormRequest $request) {
        $placesClicker = $request->clicker == "redundancy" ? "redundancy_places" : "places";
        $this->factory = new MapsFactory($placesClicker);
        
        if($this->factory) {
            $this->clicker = $this->factory->createMaps();
            $response = $this->clicker->getGeocodeWithAddress($request->address, $request->place_id, $request->lang, null, null);
        }
        else {
            $response = array("success" => false, "data" => [], "error_message" => trans('maps_lib.no_data_found'));
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
   
   
    public function geocode(GeocodeFormRequest $request) {
        $placesClicker = $request->clicker == "redundancy" ? "redundancy_places" : "places";
        $this->factory = new MapsFactory($placesClicker);
        
        if($this->factory) {
            $this->clicker = $this->factory->createMaps();
            $response = $this->clicker->getGeocodeWithAddress($request->address, $request->place_id, $request->lang, $request->latitude, $request->longitude);
        }
        else {
            $response = array("success" => false, "data" => [], "error_message" => trans('maps_lib.no_data_found'));
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

    public function geocodeReverse(GeocodeReverseFormRequest $request) {
        $placesClicker = $request->clicker == "redundancy" ? "redundancy_places" : "places";
        $this->factory = new MapsFactory($placesClicker);

        if($this->factory)
        {
            $this->clicker = $this->factory->createMaps();
            $response = $this->clicker->getGeocodeByLatLng($request->latitude, $request->longitude);
        }
        else
        {
            $response = array("success" => false, "data" => [], "error_message" => trans('maps_lib.no_data_found'));
        }

        $response['clicker'] = "primary";
        if((!$this->factory || $response['success'] == false) && GeolocationSettings::getPlacesRedundancyRule()) {
            $this->factoryRedundancy = new MapsFactory('redundancy_places');

            if($this->factoryRedundancy){
                $this->clickerRedundancy = $this->factoryRedundancy->createMaps();
                $response = $this->clickerRedundancy->getGeocodeByLatLng($request->latitude, $request->longitude);
            }

            $response['clicker'] = "redundancy";
        }

        return new GeocodeResource(["response" => $response]);
    }
    
    public function getDetailsById(PlaceDetailsFormRequest $request) {
        if(GeolocationSettings::getPlacesProvider() == "google_maps"){
            $this->factory = new MapsFactory('places');

            if($this->factory){
                $this->clicker = $this->factory->createMaps();
                $response = $this->clicker->getDetailsById($request->place_id);
            }else{
                $response = array("success" => false, "data" => [], "error_message" => trans('maps_lib.no_data_found'));
            }
        }else{
            $response = array("success" => false, "data" => [], "error_message" => trans('maps_lib.no_google_lib'));
        }
		
        return new PlaceDetailsResource(["response" => $response]);
    }
    
}
