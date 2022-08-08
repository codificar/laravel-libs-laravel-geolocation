<?php

namespace Codificar\Geolocation\Lib\Directions;

//Internal Uses
use Codificar\Geolocation\Models\GeolocationSettings;
use Codificar\Geolocation\Helper;

/**
 * Geolocation requests on MapQuest Maps API
 */
class MapsDirectionsMapQuestLib implements IMapsDirections
{

    /**
     * @var String $url_api URL to access API
     */
    private $url_api = "http://www.mapquestapi.com";

    /**
     * @var String $directions_key_api Key of API authentication
     */
    private $directions_key_api;

    /**
     * @var String $settings_dist Default distance unit
     */
    private $settings_dist;

    /**
     * @var String $unit_text Distance unit text
     */
    private static $unit_text;

    /**
     * Defined properties
     */
    public function __construct($apiKey = null)
    {
        $this->directions_key_api = $apiKey ? $apiKey : GeolocationSettings::getDirectionsKey();
        $this->settings_dist = GeolocationSettings::getDefaultDistanceUnit();
        self::$unit_text = $this->settings_dist == 1 ? trans('geolocationTrans::geolocation.mile') : trans('geolocationTrans::geolocation.km');
    }

    /**
     * Gets and calculate distance on MapQuest Maps
     *
     * @param Decimal $source_lat Decimal that represents the starting latitude of the request.
     * @param Decimal $source_long Decimal that represents the starting longitude of the request.
     * @param Decimal $dest_lat Decimal that represents the destination latitude of the request.
     * @param Decimal $dest_long Decimal that represents the destination longitude of the request.
     *
     * @return Array        ['success', 'data' => ['distance']]
     */
    public function getDistanceByDirections($source_lat, $source_long, $dest_lat, $dest_long)
    {
        if (!$this->directions_key_api) {
            return array('success' => false);
        }
        \Log::info("before quest: " . date("d/m/Y H:i:s"));
        $curl_string = $this->url_api . "/directions/v2/route?key=" . $this->directions_key_api . "&from=" . $source_lat . "," . $source_long . "&to=" . $dest_lat . "," . $dest_long . "&unit=k&timeType=1&useTraffic=true";
        $php_obj = self::curlCall($curl_string);
        $response_obj = json_decode($php_obj);
        \Log::info("after quest: " . date("d/m/Y H:i:s"));

        if (isset($response_obj->info->statuscode) && $response_obj->info->statuscode == 0) {
            $dist = convert_distance_format($this->settings_dist, $response_obj->route->distance * 1000);

            return array('success' => true, 'data' => ['distance' => $dist]);
        } else {
            return array('success' => false);
        }
    }

    /**
     * Gets and calculate distance and duration on MapQuest Maps
     *
     * @param Decimal $source_lat Decimal that represents the starting latitude of the request.
     * @param Decimal $source_long Decimal that represents the starting longitude of the request.
     * @param Decimal $dest_lat Decimal that represents the destination latitude of the request.
     * @param Decimal $dest_long Decimal that represents the destination longitude of the request.
     *
     * @return Array        ['success', 'data' => ['distance','time_in_minutes','distance_text','duration_text']]
     */
    public function getDistanceAndTimeByDirections($source_lat, $source_long, $dest_lat, $dest_long)
    {
        if (!$this->directions_key_api) {
            return array('success' => false);
        }

        $curl_string = $this->url_api . "/directions/v2/route?key=" . $this->directions_key_api . "&from=" . $source_lat . "," . $source_long . "&to=" . $dest_lat . "," . $dest_long . "&unit=k&timeType=1&useTraffic=true";
        $php_obj = self::curlCall($curl_string);
        $response_obj = json_decode($php_obj);

        if (isset($response_obj->info->statuscode) && $response_obj->info->statuscode == 0) {
            $dist = convert_distance_format($this->settings_dist, $response_obj->route->legs[0]->distance * 1000);
            $time_in_minutes = convert_to_minutes($response_obj->route->legs[0]->time);

            $distance_text = number_format($response_obj->route->legs[0]->distance, 1) . " " . self::$unit_text;
            $duration_text = ceil(convert_to_minutes($response_obj->route->legs[0]->time)) . " " . trans('geolocationTrans::geolocation.minutes');

            return array('success' => true, 'data' => ['distance' => $dist, 'time_in_minutes' => $time_in_minutes, 'distance_text' => $distance_text, 'duration_text' => $duration_text]);
        } else {
            return array('success' => false);
        }
    }

    /**
     * Return intermediaries multiple points in the route using MapQuest Maps
     *
     * @param Decimal $source_lat Decimal that represents the starting latitude of the request.
     * @param Decimal $source_long Decimal that represents the starting longitude of the request.
     * @param Decimal $dest_lat Decimal that represents the destination latitude of the request.
     * @param Decimal $dest_long Decimal that represents the destination longitude of the request.
     *
     * @return Array        ['points' => [['lat','lng']['lat','lng']...],'distance_text','duration_text','distance_value','duration_value']
     */
    public function getPolylineAndEstimateByDirections($source_lat, $source_long, $dest_lat, $dest_long)
    {
        if (!$this->directions_key_api) {
            return false;
        }

        $curl_string = $this->url_api . "/directions/v2/route?key=" . $this->directions_key_api . "&from=" . $source_lat . "," . $source_long . "&to=" . $dest_lat . "," . $dest_long . "&unit=k&timeType=1&useTraffic=true&fullShape=true";

        return self::polylineProcess($curl_string);
    }

    /**
     * Return intermediaries multiple points in the route using MapQuest Maps
     *
     * @param String $source_address String that represents the starting address of the request.
     * @param String $destination_address String that represents the destination address of the request.
     * @param Decimal $source_lat Decimal that represents the starting latitude of the request.
     * @param Decimal $source_long Decimal that represents the starting longitude of the request.
     * @param Decimal $dest_lat Decimal that represents the destination latitude of the request.
     * @param Decimal $dest_long Decimal that represents the destination longitude of the request.
     *
     * @return Array       ['points' => [['lat','lng']['lat','lng']...],'distance_text','duration_text','distance_value','duration_value']
     */
    public function getPolylineAndEstimateByAddresses($source_address, $destination_address, $source_lat = false, $source_long = false, $dest_lat = false, $dest_long = false)
    {
        if (!$this->directions_key_api) {
            return false;
        }

        $curl_string = $this->url_api . "/directions/v2/route?key=" . $this->directions_key_api . "&from=" . urlencode($source_address) . "&to=" . urlencode($destination_address) . "&unit=k&timeType=1&useTraffic=true&fullShape=true";

        return self::polylineProcess($curl_string);
    }

    /**
     * Creates and call request by curl client
     *
     * @param String $curl_string URL called on curl request.
     *
     * @return Object      $msg_chk             Response on curl request
     */
    private static function curlCall($curl_string)
    {
        $session = curl_init($curl_string);
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
        $msg_chk = curl_exec($session);

        return $msg_chk;
    }

    /**
     * Format and return duration as text
     *
     * @param Double $duration Duration of request in minutes.
     *
     * @return String      $duration_format     Duration format in hours and minutes.
     */
    private static function formatTime($duration)
    {
        $duration = ceil(convert_to_minutes($duration));
        if ($duration > 60) {
            $hours_full = $duration / 60;
            $minutes_full = $hours_full - floor($hours_full);
            $minutes_float = (($minutes_full * 100) * 60) / 100;
            $minutes = ceil($minutes_float);

            $duration_format = floor($hours_full) . "h" . $minutes;
        } else {
            $duration_format = $duration;
        }

        return $duration_format . trans('geolocationTrans::geolocation.minutes');
    }

    /**
     * Process curl response and return array with polyline and estimates.
     *
     * @param String $curl_string URL called by curl.
     *
     * @return Array      $polyline            Array with polyline and estimates.
     */
    private static function polylineProcess($curl_string)
    {
        $php_obj = self::curlCall($curl_string);
        $response_obj = json_decode($php_obj, true);

        $polyline = array('points' => array(0 => ['lat' => '', 'lng' => '']));
        $position = 0;

        if (isset($response_obj['info']['statuscode']) && $response_obj['info']['statuscode'] == 0) {
            foreach ($response_obj['route']['shape']['shapePoints'] as $index => $point) {
                if ($index % 2 == 0) {
                    $polyline['points'][$position]['lat'] = $point;
                } else {
                    $polyline['points'][$position]['lng'] = $point;
                    $position++;
                }
            }

            $polyline['distance_text'] = number_format($response_obj['route']['legs'][0]['distance'], 1) . self::$unit_text;
            $polyline['duration_text'] = self::formatTime($response_obj['route']['legs'][0]['time']);
            $polyline['distance_value'] = $response_obj['route']['legs'][0]['distance'];
            $polyline['duration_value'] = convert_to_minutes($response_obj['route']['legs'][0]['time']);
        } else {
            return false;
        }

        return $polyline;
    }

    /**
     * Returns intermediaries points in the route between multiple locations using MapQuest Maps
     *
     * @param String $wayPoints Array with mutiples decimals thats represent the latitude and longitude of the points in the route.
     * @param Boolean $shortestDistance
     * @return Array        ['points' => [['lat','lng']['lat','lng']...],'distance_text','duration_text','distance_value','duration_value','partial_distances','partial_durations']
     */
    public function getPolylineAndEstimateWithWayPoints($wayPoints, $optimize = 0, $shortestDistance = null)
    {
        $waysFormatted = [];
        if (!$this->directions_key_api || (!is_string($wayPoints) || !is_array(json_decode($wayPoints, true)))) {
            return false;
        }

        $ways = json_decode($wayPoints);
        $waysLen = count($ways);
        if ($waysLen < 2) {
            return false;
        } else {

            $waysFormatted = '{"locations": [';

            foreach ($ways as $index => $way) {
                $waysFormatted .= '{"latLng":{"lat":' . $way[0] . ',"lng":' . $way[1] . '}},';
            }
            $waysFormatted = rtrim($waysFormatted, ",");

            $postFields = $waysFormatted . '],
                "options": {
                    "fullShape": true,
                    "shapeFormat": "raw",
                    "unit": "k",
                    "timeType": 1' .
            $shortestDistance ? ',"routingMode":"shortest"' : ' '
                . '}}';

        }
        $curl_string = $this->url_api . "/directions/v2/route?key=" . $this->directions_key_api;

        return $this->polylineProcessWithPoints($curl_string, 'post', $postFields);
    }

    /**
     * Process curl response and return array with polyline and estimates.
     *
     * @param String $curl_string URL called by curl.
     * @param String $verb Defines the request verb.
     * @param String $postFields Params to POST request.
     *
     * @return Array      $polyline         Array with polyline and estimates.
     */
    private function polylineProcessWithPoints($curl_string, $verb = null, $postFields = null)
    {
        $php_obj = $this->curlCall($curl_string, $verb, $postFields);
        $response_obj = json_decode($php_obj, true);

        $polyline = array('points' => array(0 => ['lat' => '', 'lng' => '']));
        $position = 0;

        if (isset($response_obj['info']['statuscode']) && $response_obj['info']['statuscode'] == 0) {
            foreach ($response_obj['route']['shape']['shapePoints'] as $index => $point) {
                if ($index % 2 == 0) {
                    $polyline['points'][$position]['lat'] = $point;
                } else {
                    $polyline['points'][$position]['lng'] = $point;
                    $position++;
                }
            }

            $partialDistances = [];
            $partialDurations = [];
            $totalDistance = 0;
            $totalDuration = 0;

            foreach ($response_obj['route']['legs'] as $index => $leg) {
                $partialDistances[$index] = number_format(($leg['distance'] / 1000), 2);
                $partialDurations[$index] = number_format(($leg['time'] / 60), 2);
                $totalDistance += $leg['distance'];
                $totalDuration += $leg['time'];
            }

            $polyline['distance_text'] = number_format($totalDistance, 1) . self::$unit_text;
            $polyline['duration_text'] = self::formatTime($totalDuration);
            $polyline['distance_value'] = $totalDistance;
            $polyline['duration_value'] = convert_to_minutes($totalDuration);
            $polyline['partial_distances'] = $partialDistances;
            $polyline['partial_durations'] = $partialDurations;
            $polyline['waypoint_order'] = [];
        } else {
            return false;
        }

        return $polyline;
    }

    private static function curlCallJson($curl_string, $payload)
    {
        $session = curl_init($curl_string);
        curl_setopt($session, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($session, CURLOPT_POST, true);
        curl_setopt($session, CURLOPT_POSTFIELDS, $payload);
        $msg_chk = curl_exec($session);

        return $msg_chk;
    }

    /**
     * Get the matrix distance in providers list
     *
     * @param Array $providers Array of providers.
     * @param Decimal $sourceLat Decimal that represents the starting latitude of the request.
     * @param Decimal $sourceLong Decimal that represents the starting longitude of the request.
     *
     * @return Array
     */
    public function getMatrixDistance($providers, $sourceLat, $sourceLong)
    {
        try {
            if (!$this->directions_key_api)
                return false;

            $destinations = $this->mountMatrixString($providers, $sourceLat, $sourceLong);
            $curlString = $this->url_api . "/directions/v2/routematrix?key=" . $this->directions_key_api;

            $callApi = self::curlCallJson($curlString, json_encode($destinations));
            $response = json_decode($callApi, true);

            $return = array('success' => false);

            if (is_array($response) && array_key_exists('distance', $response)) {
                $data = $response['distance'];
                $return['success'] = true;
                $return['distance'] = [];

                array_shift($data);
                foreach ($data as $item) {
                    array_push($return['distance'], $item);
                }
            }

            return $return;
        } catch (\Throwable $th) {
            \Log::error($th->getMessage());
            return array('success' => false);
        }
    }

    /**
     * Mount destinations string for matrix
     *
     * @param array $providers
     * @return array
     */
    public function mountMatrixString($providers, $sourceLat, $sourceLong)
    {
        try {
            $data = [
                "locations" => [[
                    "latLng" => [
                        "lat" => $sourceLat,
                        "lng" => $sourceLong
                    ]
                ]]
            ];

            for ($i = 0; $i < count($providers); $i++) {
                $location = [
                    "latLng" => [
                        "lat" => $providers[$i]->latitude,
                        "lng" => $providers[$i]->longitude
                    ]
                ];

                array_push($data["locations"], $location);
            }

            return $data;
        } catch (\Throwable $th) {
            \Log::error($th->getMessage());
            return ["locations" => []];
        }
    }

    /**
     * Gets static map containing the route especified by paht parameter;
     *
     * @param array $points points in the request's route
     * @param int $with map width size
     * @param int $height map height size
     *
     * @return String    url
     */
    public function getStaticMapByPath(array $points, int $width = 249, int $height = 246)
    {
        $url = $this->url_api
            . "/staticmap/v5/map?key=" . $this->directions_key_api
            . "&size=" . $width . ',' . $height
            . "&locations=" . $points[0] . "|marker-start"
            . '||' . $points[count($points) - 1] . "|marker-end"
            . "&shape=";
        foreach ($points as $point) $url .= $point . '|';

        return $url;
    }
}