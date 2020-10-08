<?php

namespace Codificar\Geolocation\Http\Controllers;

//Laravel Uses
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use View;

//Internal Uses
use App\Http\Requests\api\v3\PlacesFormRequest;
use App\Http\Requests\api\v3\GeocodeFormRequest;
use App\Http\Resources\api\v3\PlacesResource;
use App\Http\Resources\api\v3\GeocodeResource;

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
   
   
    public function geocode(GeocodeFormRequest $request) {
        $placesClicker = $request->clicker == "redundancy" ? "redundancy_places" : "places";
        $this->factory = new MapsFactory($placesClicker);

        if($this->factory) {
            $this->clicker = $this->factory->createMaps();
            $response = $this->clicker->getGeocodeWithAddress($request->address, $request->place_id, $request->lang);
        }
        else {
            $response = array("success" => false, "data" => [], "error_message" => trans('maps_lib.no_data_found'));
        }

        if($response['success']) $response['data']['address'] = $request->address;

        return new GeocodeResource(["response" => $response]);
    }    
    
    public function autocompleteTest(){
        $this->factory = new MapsFactory('places');
        $this->clicker = $this->factory->createMaps();
        $place = "Rua alto do tanque";
        $latitude = -19.743745;
        $longitude = -43.8386481;
        $response = $this->clicker->getAddressByTextWithLatLng($place, $latitude, $longitude);
    
        return $response;
    }

    public function geocodeTeste(){
        $this->factory = new MapsFactory('places');
        $this->clicker = $this->factory->createMaps();
        $place = "Rua alto do tanque";
        $latitude = -19.743745;
        $longitude = -43.8386481;
        $response =  $this->clicker->getGeocodeWithAddress($place, $latitude, $longitude);
    
        return $response;
    }
    
}
