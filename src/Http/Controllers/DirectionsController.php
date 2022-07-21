<?php

namespace Codificar\Geolocation\Http\Controllers;

//Laravel Uses
use App\Http\Controllers\Controller;
use Codificar\Geolocation\Http\Requests\GetStaticMapByPathFormRequest;
use Codificar\Geolocation\Http\Requests\RouteWayPointsRequest;
use Codificar\Geolocation\Http\Resources\GetStaticMapResource;
use Codificar\Geolocation\Http\Resources\PolylineResource;
use Codificar\Geolocation\Lib\MapsFactory;
use Codificar\Geolocation\Models\GeolocationSettings;
use Illuminate\Http\Request;
use View;

//Internal Uses

// use MapsFactory, Settings;

class DirectionsController extends Controller
{
    /**
     * Gets distance and time by directions API.
     *
     * @param Decimal $source_lat Decimal that represents the starting latitude of the request.
     * @param Decimal $source_long Decimal that represents the starting longitude of the request.
     * @param Decimal $dest_lat Decimal that represents the destination latitude of the request.
     * @param Decimal $dest_long Decimal that represents the destination longitude of the request.
     *
     * @return Array        ['success', 'data' => ['distance','time_in_minutes','distance_text','duration_text']]
     */
    public function getDirectionsDistanceAndTime($startLat, $startLng, $destLat, $destLng)
    {
        $factory = new MapsFactory('directions');
        $clicker = $factory->createMaps();

        if ($clicker) {
            $requestTimeDistance = $clicker->getDistanceAndTimeByDirections(
                $startLat, $startLng, $destLat, $destLng
            );
        }

        if ((!isset($requestTimeDistance['success']) || !$requestTimeDistance['success']) && GeolocationSettings::getDirectionsRedundancyRule()) {
            $factoryRedundancy = new MapsFactory('redundancy_directions');

            if ($factoryRedundancy) {
                $clickerRedundancy = $factoryRedundancy->createMaps();

                if ($clickerRedundancy)
                    $requestTimeDistance = $clickerRedundancy->getDistanceAndTimeByDirections(
                        $startLat, $startLng, $destLat, $destLng
                    );
            }
        }

        return $requestTimeDistance;
    }

    /**
     * Gets polyline and estimate route by directions API.
     *
     * @param Decimal $source_lat Decimal that represents the starting latitude of the request.
     * @param Decimal $source_long Decimal that represents the starting longitude of the request.
     * @param Decimal $dest_lat Decimal that represents the destination latitude of the request.
     * @param Decimal $dest_long Decimal that represents the destination longitude of the request.
     *
     * @return Array        ['points' => [['lat','lng']['lat','lng']...],'distance_text','duration_text','distance_value','duration_value']
     */

    public function getPolylineAndEstimateByDirections($startLat, $startLng, $destLat, $destLng)
    {
        $factory = new MapsFactory('directions');
        $clicker = $factory->createMaps();

        if ($clicker) {
            $requestTimeDistance = $clicker->getPolylineAndEstimateByDirections(
                $startLat, $startLng, $destLat, $destLng
            );
        }

        if ((!is_array($requestTimeDistance) || !$requestTimeDistance) && GeolocationSettings::getDirectionsRedundancyRule()) {
            $factoryRedundancy = new MapsFactory('redundancy_directions');

            if ($factoryRedundancy) {
                $clickerRedundancy = $factoryRedundancy->createMaps();

                if ($clickerRedundancy)
                    $requestTimeDistance = $clickerRedundancy->getPolylineAndEstimateByDirections(
                        $startLat, $startLng, $destLat, $destLng
                    );
            }
        }

        return $requestTimeDistance;
    }

    /**
     * Gets polyline and estimate route by adresses in directions API.
     *
     * @param String $srcAddress String that represents the starting address of the request.
     * @param String $destAddress String that represents the destination address of the request.
     * @param Decimal $startLat Decimal that represents the starting latitude of the request.
     * @param Decimal $startLng Decimal that represents the starting longitude of the request.
     * @param Decimal $destLat Decimal that represents the destination latitude of the request.
     * @param Decimal $destLng Decimal that represents the destination longitude of the request.
     *
     * @return Array        ['points' => [['lat','lng']['lat','lng']...],'distance_text','duration_text','distance_value','duration_value']
     */

    public function getPolylineAndEstimateByAddresses($srcAddress, $destAddress, $startLat, $startLng, $destLat, $destLng)
    {
        $factory = new MapsFactory('directions');
        $clicker = $factory->createMaps();

        if ($clicker) {
            $requestTimeDistance = $clicker->getPolylineAndEstimateByAddresses(
                $srcAddress, $destAddress, $startLat, $startLng, $destLat, $destLng
            );
        }

        if ((!is_array($requestTimeDistance) || !$requestTimeDistance) && GeolocationSettings::getDirectionsRedundancyRule()) {
            $factoryRedundancy = new MapsFactory('redundancy_directions');

            if ($factoryRedundancy) {
                $clickerRedundancy = $factoryRedundancy->createMaps();

                if ($clickerRedundancy)
                    $requestTimeDistance = $clickerRedundancy->getPolylineAndEstimateByAddresses(
                        $srcAddress, $destAddress, $startLat, $startLng, $destLat, $destLng
                    );
            }
        }

        return $requestTimeDistance;
    }

    /**
     * Gets polyline and estimate route by adresses in directions API.
     *
     * @param String $waypoints : "[[lat, lng]['lat','lng']...]"
     * @param boolean $shortestDistance "
     *
     * @return Array        ['points' => [['lat','lng']['lat','lng']...],'distance_text','duration_text','distance_value','duration_value']
     */

    public static function getPolylineAndEstimateWithWayPoints($allPointsAPI, $shortestDistance = null)
    {
        $factory = new MapsFactory('directions');
        $clicker = $factory->createMaps();

        if ($clicker)
            $response = $clicker->getPolylineAndEstimateWithWayPoints(
                $allPointsAPI,
                shortestDistance: $shortestDistance
            );

        if ((!is_array($response) || !$response) && GeolocationSettings::getDirectionsRedundancyRule()) {
            $factoryRedundancy = new MapsFactory('redundancy_directions');

            if ($factoryRedundancy) {
                $clickerRedundancy = $factoryRedundancy->createMaps();
                $response = $clickerRedundancy->getPolylineAndEstimateWithWayPoints(
                    $allPointsAPI
                );
            }
        }
        return $response;
    }

    public function getDirectionsDistanceAndTimeApi(Request $request)
    {
        $startLat = $request->startLat;
        $startLng = $request->startLng;
        $destLat = $request->destLat;
        $destLng = $request->destLng;

        $factory = new MapsFactory('directions');
        $clicker = $factory->createMaps();

        if ($clicker) {
            $requestTimeDistance = $clicker->getDistanceAndTimeByDirections(
                $startLat, $startLng, $destLat, $destLng
            );
        }

        if ((!isset($requestTimeDistance['success']) || !$requestTimeDistance['success']) && GeolocationSettings::getDirectionsRedundancyRule()) {
            $factoryRedundancy = new MapsFactory('redundancy_directions');

            if ($factoryRedundancy) {
                $clickerRedundancy = $factoryRedundancy->createMaps();

                if ($clickerRedundancy)
                    $requestTimeDistance = $clickerRedundancy->getDistanceAndTimeByDirections(
                        $startLat, $startLng, $destLat, $destLng
                    );
            }
        }

        return $requestTimeDistance;
    }

    public function getPolylineAndEstimateByDirectionsApi(Request $request)
    {
        $startLat = $request->startLat;
        $startLng = $request->startLng;
        $destLat = $request->destLat;
        $destLng = $request->destLng;


        $factory = new MapsFactory('directions');
        $clicker = $factory->createMaps();
        if ($clicker) {
            $requestTimeDistance = $clicker->getPolylineAndEstimateByDirections(
                $startLat, $startLng, $destLat, $destLng
            );
        }

        if ((!is_array($requestTimeDistance) || !$requestTimeDistance) && GeolocationSettings::getDirectionsRedundancyRule()) {
            $factoryRedundancy = new MapsFactory('redundancy_directions');

            if ($factoryRedundancy) {
                $clickerRedundancy = $factoryRedundancy->createMaps();

                if ($clickerRedundancy)
                    $requestTimeDistance = $clickerRedundancy->getPolylineAndEstimateByDirections(
                        $startLat, $startLng, $destLat, $destLng
                    );
            }
        }

        return $requestTimeDistance;
    }

    public function getPolylineAndEstimateByAddressesApi(Request $request)
    {
        $srcAddress = $request->srcAddress;
        $destAddress = $request->destAddress;
        $startLat = $request->startLat;
        $startLng = $request->startLng;
        $destLat = $request->destLat;
        $destLng = $request->destLng;

        $factory = new MapsFactory('directions');
        $clicker = $factory->createMaps();

        if ($clicker) {
            $requestTimeDistance = $clicker->getPolylineAndEstimateByAddresses(
                $srcAddress, $destAddress, $startLat, $startLng, $destLat, $destLng
            );
        }

        if ((!is_array($requestTimeDistance) || !$requestTimeDistance) && GeolocationSettings::getDirectionsRedundancyRule()) {
            $factoryRedundancy = new MapsFactory('redundancy_directions');

            if ($factoryRedundancy) {
                $clickerRedundancy = $factoryRedundancy->createMaps();

                if ($clickerRedundancy)
                    $requestTimeDistance = $clickerRedundancy->getPolylineAndEstimateByAddresses(
                        $srcAddress, $destAddress, $startLat, $startLng, $destLat, $destLng
                    );
            }
        }

        return $requestTimeDistance;
    }


    public function getPolylineAndEstimateWithWayPointsApi(RouteWayPointsRequest $request)
    {
        $factory = new MapsFactory('directions');
        $clicker = $factory->createMaps();
        $response = false;
        $error = '';
        $optimizeRoute = (isset($request->optimize_route) && $request->optimize_route == 1) ? 1 : 0;
        $shortestDistance = \Settings::findByKey('directions_shortest_distance');
        if ($clicker) {
            $response = $clicker->getPolylineAndEstimateWithWayPoints(
                $request->waypoints,
                $optimizeRoute,
                shortestDistance: $shortestDistance
            );
        }
        if ((!is_array($response) || !$response) && GeolocationSettings::getDirectionsRedundancyRule()) {
            $factoryRedundancy = new MapsFactory('redundancy_directions');

            if ($factoryRedundancy) {
                $clickerRedundancy = $factoryRedundancy->createMaps();
                $response = $clickerRedundancy->getPolylineAndEstimateWithWayPoints(
                    $request->waypoints,
                    $optimizeRoute
                );
            }
        }

        if ($response) {
            $success = true;
            $polyline = $response['points'];
            $distanceText = $response['distance_text'];
            $durationText = $response['duration_text'];
            $distanceValue = $response['distance_value'];
            $durationValue = $response['duration_value'];
            $partialDistances = $response['partial_distances'];
            $partialDurations = $response['partial_durations'];

            //if has waypoint ordering
            if (isset($response['waypoint_order']) && $response['waypoint_order']) {
                $waypointOrder = $response['waypoint_order'];
            } else {
                $waypointOrder = [];
            }
        } else {
            $success = false;
            $polyline = '';
            $distanceText = '';
            $durationText = '';
            $distanceValue = '';
            $durationValue = '';
            $partialDistances = [];
            $partialDurations = [];
            $waypointOrder = [];
            $error = trans('geolocationTrans::geolocation.no_data_found');
        }

        $response_array = array(
            'success' => $success,
            'points' => $polyline,
            'distance_text' => $distanceText,
            'duration_text' => $durationText,
            'distance_value' => $distanceValue,
            'duration_value' => $durationValue,
            'partial_distances' => $partialDistances,
            'partial_durations' => $partialDurations,
            'waypoint_order' => $waypointOrder,
            'error' => $error
        );

        return new PolylineResource(['data' => $response_array]);
    }

    public function getStaticMap(GetStaticMapByPathFormRequest $request)
    {
        $factory = new MapsFactory('directions');
        $clicker = $factory->createMaps();
        if ($clicker) {
            dd($clicker->getStaticMapByPath($request->get('points'), $request->get('width'), $request->get('height')));
            return new GetStaticMapResource(
                [
                    'data' => $clicker->getStaticMap($request->all()),
                    'sucess' => true

                ]);
        }else{
            return new GetStaticMapResource(
                [
                    'error' => trans('geolocationTrans::geolocation.no_data_found'),
                    'sucess' => false

                ]);
           }
    }

    public function getStaticMapByPath(array $points, int $with = 520, int $height=520)
    {
        $factory = new MapsFactory('directions');
        $clicker = $factory->createMaps();
        if ($clicker) {
            return  $clicker->getStaticMapByPath($points, $with, $height);
        }
    }

}
