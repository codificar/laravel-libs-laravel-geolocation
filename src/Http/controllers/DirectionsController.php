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

use Codificar\Geolocation\Http\Resources\PlacesResource;
use Codificar\Geolocation\Http\Resources\GeocodeResource;
use Codificar\Geolocation\Http\Resources\PlaceDetailsResource;

// use MapsFactory, Settings;
use Codificar\Geolocation\Lib\MapsFactory;
use Codificar\Geolocation\Models\GeolocationSettings;

class DirectionsController extends Controller {      
    //Directions    
    public function getDirectionsDistanceAndTime($startLat, $startLng, $destLat, $destLng) {      
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

        return $requestTimeDistance;
    }

    public function getPolylineAndEstimateByDirections($startLat, $startLng, $destLat, $destLng) {      
        $factory = new MapsFactory('directions');
        $clicker = $factory->createMaps();

        if($clicker){
            $requestTimeDistance = $clicker->getPolylineAndEstimateByDirections(
                $startLat, $startLng, $destLat, $destLng
            );
        }
        
        if((!isset($requestTimeDistance['success']) || !$requestTimeDistance['success']) && GeolocationSettings::getDirectionsRedundancyRule()){
            $factoryRedundancy = new MapsFactory('redundancy_directions');

            if($factoryRedundancy){
                $clickerRedundancy = $factoryRedundancy->createMaps();

                if($clickerRedundancy)
                    $requestTimeDistance = $clickerRedundancy->getPolylineAndEstimateByDirections(
                        $startLat, $startLng, $destLat, $destLng
                    );
            }
        }

        return $requestTimeDistance;
    }

    public function getPolylineAndEstimateByAddresses($srcAddress, $destAddress, $startLat, $startLng, $destLat, $destLng) {      
        $factory = new MapsFactory('directions');
        $clicker = $factory->createMaps();

        if($clicker){
            $requestTimeDistance = $clicker->getPolylineAndEstimateByAddresses(
               $srcAddress, $destAddress, $startLat, $startLng, $destLat, $destLng
            );
        }
        
        if((!isset($requestTimeDistance['success']) || !$requestTimeDistance['success']) && GeolocationSettings::getDirectionsRedundancyRule()){
            $factoryRedundancy = new MapsFactory('redundancy_directions');

            if($factoryRedundancy){
                $clickerRedundancy = $factoryRedundancy->createMaps();

                if($clickerRedundancy)
                    $requestTimeDistance = $clickerRedundancy->getPolylineAndEstimateByAddresses(
                        $srcAddress, $destAddress, $startLat, $startLng, $destLat, $destLng
                    );
            }
        }

        return $requestTimeDistance;
    }

}
