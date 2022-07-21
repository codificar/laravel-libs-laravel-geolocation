<?php

namespace Codificar\Geolocation\Lib\Directions;

//Internal Uses
use Codificar\Geolocation\Http\Resources\GetStaticMapResource;
use Codificar\Geolocation\Models\GeolocationSettings;
use Codificar\Geolocation\Helper;

//External Uses
use GeometryLibrary\PolyUtil;
use phpDocumentor\Reflection\Types\Boolean;

/**
 * Geolocation requests on Google Maps API
 */
class MapsDirectionsGoogleLib implements IMapsDirections
{

    /**
     * @var String $url_api URL to access API
     */
    private $url_api = "https://maps.googleapis.com/maps/api";

    /**
     * @var String $static_map_url URL to access static maps  API
     */
    private $static_map_url = "https://maps.googleapis.com/maps/api/staticmap";

    /**
     * @const int DEFAULT_ZOOM zoom used in static maps lib
     */
    const DEFAULT_ZOOM = 15;//city level

    /**
     * @var String $directions_key_api Key of API authentication
     */
    private $directions_key_api;

    /**
     * @var String $settings_dist Default distance unit
     */
    private static $settings_dist;

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
        self::$settings_dist = 0;
        self::$unit_text = trans('geolocationTrans::geolocation.km');
    }

    /**
     * Gets and calculate distance on Google Maps
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
        \Log::info("before google: " . date("d/m/Y H:i:s"));
        $curl_string = $this->url_api . "/directions/json?origin=" . $source_lat . "," . $source_long . "&destination=" . $dest_lat . "," . $dest_long . "&key=" . $this->directions_key_api . "";
        $php_obj = self::curlCall($curl_string);
        $response_obj = json_decode($php_obj);
        \Log::info("after google: " . date("d/m/Y H:i:s"));

        if ($response_obj->status && $response_obj->status == 'OK') {
            $dist = convert_distance_format(0, $response_obj->routes[0]->legs[0]->distance->value);

            return array('success' => true, 'data' => ['distance' => $dist]);
        } else {
            return array('success' => false);
        }
    }

    /**
     * Gets and calculate distance and duration on Google Maps
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

        $curl_string = $this->url_api . "/directions/json?origin=" . $source_lat . "," . $source_long . "&destination=" . $dest_lat . "," . $dest_long . "&key=" . $this->directions_key_api . "";
        $php_obj = self::curlCall($curl_string);
        $response_obj = json_decode($php_obj);

        if ($response_obj->status && $response_obj->status == 'OK') {
            $dist = convert_distance_format(0, $response_obj->routes[0]->legs[0]->distance->value);
            $time_in_minutes = convert_to_minutes($response_obj->routes[0]->legs[0]->duration->value);

            $distance_text = number_format(convert_distance_format(0, $response_obj->routes[0]->legs[0]->distance->value), 1) . " " . self::$unit_text;
            $duration_text = ceil(convert_to_minutes($response_obj->routes[0]->legs[0]->duration->value)) . " " . trans('geolocationTrans::geolocation.minutes');

            return array('success' => true, 'data' => ['distance' => $dist, 'time_in_minutes' => $time_in_minutes, 'distance_text' => $distance_text, 'duration_text' => $duration_text]);
        } else {
            return array('success' => false);
        }
    }

    /**
     * Return intermediaries multiple points in the route using Google Maps
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

        $curl_string = $this->url_api . "/directions/json?origin=" . $source_lat . "," . $source_long . "&destination=" . $dest_lat . "," . $dest_long . "&key=" . $this->directions_key_api . "";

        return self::polylineProcess($curl_string);
    }

    /**
     * Return intermediaries multiple points in the route using Google Maps
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

        $curl_string = $this->url_api . "/directions/json?origin=" . urlencode($source_address) . "&destination=" . urlencode($destination_address) . "&key=" . $this->directions_key_api . "";

        return self::polylineProcess($curl_string);
    }

    /**
     * Creates and call request by curl client
     *
     * @param String $curl_string URL called on curl request.
     *
     * @return Object      $msg_chk             Response json on curl request
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

        return $duration_format . ' ' . trans('geolocationTrans::geolocation.minutes');
    }

    /**
     * Process curl response and return array with polyline and estimates.
     *
     * @param String $curl_string URL called by curl.
     *
     * @return Array      $array_resp          Array with polyline and estimates.
     */
    private static function polylineProcess($curl_string)
    {
        $php_obj = self::curlCall($curl_string);
        $response_obj = json_decode($php_obj, true);

        if ($response_obj['status'] && $response_obj['status'] == 'OK') {
            $polyline['points'] = $response_obj['routes'][0]['overview_polyline']['points'];

            $needle = metaphone('points');

            // get polyline response
            $obj = $polyline;

            // flatten array into single level array using 'dot' notation
            $obj_dot = array_dot($obj);
            // create empty array_resp
            $array_resp = [];
            // iterate
            foreach ($obj_dot as $key => $val) {
                // Calculate the metaphone key and compare with needle
                $val = strcmp(metaphone($key, strlen($needle)), $needle) === 0
                    ? PolyUtil::decode($val) // if matched decode polyline
                    : $val;
                array_set($array_resp, $key, $val);
            }

            $array_resp['distance_text'] = number_format(convert_distance_format(0, $response_obj['routes'][0]['legs'][0]['distance']['value']), 1) . ' ' . self::$unit_text;
            $array_resp['duration_text'] = self::formatTime($response_obj['routes'][0]['legs'][0]['duration']['value']);
            $array_resp['distance_value'] = convert_distance_format(0, $response_obj['routes'][0]['legs'][0]['distance']['value']);
            $array_resp['duration_value'] = convert_to_minutes($response_obj['routes'][0]['legs'][0]['duration']['value']);
        } else {
            return false;
        }

        return $array_resp;
    }

    /**
     * Returns intermediaries points in the route between multiple locations using Google Maps
     *
     * @param String $wayPoints Array with mutiples decimals thats represent the latitude and longitude of the points in the route.
     * @param Boolean $shortestDistance
     * @return Array        ['points' => [['lat','lng']['lat','lng']...],'distance_text','duration_text','distance_value','duration_value','partial_distances','partial_durations']
     */
    public function getPolylineAndEstimateWithWayPoints($wayPoints, $optimize = 0, $shortestDistance = null)
    {

        $waysFormatted = '';
        if (!$this->directions_key_api || (!is_string($wayPoints) || !is_array(json_decode($wayPoints, true)))) {
            \Log::info("getPolylineAndEstimateWithWayPoints:false");
            return false;
        }

        $ways = json_decode($wayPoints);
        $waysLen = count($ways);
        if ($optimize == 1) {
            $optimizeRoute = "optimize:true|";
        } else {
            $optimizeRoute = "optimize:false|";
        }

        if ($waysLen > 2) {
            foreach ($ways as $index => $way) {
                if ($index != 0 && $index < ($waysLen - 1))
                    $waysFormatted .= !$waysFormatted ? ("&waypoints=" . $optimizeRoute . $way[0] . "," . $way[1]) : "|" . $way[0] . "," . $way[1];
            }

            $waysFormatted = rtrim($waysFormatted, "|");
        } else if ($waysLen < 2) {
            return false;
        }

        $google_key = $this->directions_key_api;

        $curl_string = $this->url_api . "/directions/json?key=" . $google_key .
            "&origin=" . urlencode($ways[0][0] . "," . $ways[0][1]) .
            "&destination="
            . urlencode($ways[$waysLen - 1][0] . "," . $ways[$waysLen - 1][1])
            . $waysFormatted;
        if ($shortestDistance) $curl_string = $curl_string . '&alternatives=true';

        return self::polylineProcessWithPoints($curl_string);
    }

    /**
     * Process curl response and return array with polyline and estimates.
     *
     * @param String $curl_string URL called by curl.
     *
     * @return Array      $array_resp          Array with polyline and estimates.
     */
    private static function polylineProcessWithPoints($curl_string)
    {
        $php_obj = self::curlCall($curl_string);
        $response_obj = json_decode($php_obj, true);
        if ($response_obj['status'] && $response_obj['status'] == 'OK') {
            $polyline['points'] = $response_obj['routes'][0]['overview_polyline']['points'];

            $shortestRoute = $response_obj['routes'][0];
            for ($i = 1; $i < sizeof($response_obj['routes']); $i++) {
                if ($response_obj['routes'][$i]['legs'][0]['distance']['value'] < $shortestRoute['legs'][0]['distance']['value'])
                    $shortestRoute = $response_obj['routes'][$i];

            }


            // Get the waypoint order, (needs if has optimize route)
            $waypoint_order = $shortestRoute['waypoint_order'];

            $needle = metaphone('points');

            // get polyline response
            $obj = $polyline;

            // flatten array into single level array using 'dot' notation
            $obj_dot = array_dot($obj);
            // create empty array_resp
            $array_resp = [];
            // iterate
            foreach ($obj_dot as $key => $val) {
                // Calculate the metaphone key and compare with needle
                $val = strcmp(metaphone($key, strlen($needle)), $needle) === 0
                    ? PolyUtil::decode($val) // if matched decode polyline
                    : $val;
                array_set($array_resp, $key, $val);
            }

            $partialDistances = [];
            $partialDurations = [];
            $totalDistance = 0;
            $totalDuration = 0;

            foreach ($shortestRoute['legs'] as $index => $leg) {
                $partialDistances[$index] = number_format(($leg['distance']['value'] / 1000), 2);
                $partialDurations[$index] = number_format(($leg['duration']['value'] / 60), 2);

                $totalDistance += $leg['distance']['value'];
                $totalDuration += $leg['duration']['value'];
            }

            $array_resp['distance_text'] = number_format(convert_distance_format(0, $totalDistance), 2) . self::$unit_text;
            $array_resp['duration_text'] = self::formatTime($totalDuration);
            $array_resp['distance_value'] = convert_distance_format(0, $totalDistance);
            $array_resp['duration_value'] = convert_to_minutes($totalDuration);
            $array_resp['partial_distances'] = $partialDistances;
            $array_resp['partial_durations'] = $partialDurations;

            if (isset($waypoint_order) && $waypoint_order) {
                $array_resp['waypoint_order'] = $waypoint_order;
            } else {
                $array_resp['waypoint_order'] = [];
            }

        } else {
            return false;
        }

        return $array_resp;
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
            $destinations = $this->mountMatrixString($providers);
            $curlString = $this->url_api . "/distancematrix/json?key=" .
                $this->directions_key_api .
                "&origins=$sourceLat,$sourceLong" .
                "&destinations=$destinations";

            $callApi = self::curlCall($curlString);
            $response = json_decode($callApi, true);

            $return = array('success' => false);

            if (is_array($response) && array_key_exists('status', $response) && $response['status'] == 'OK') {
                $data = $response['rows'][0]['elements'];
                $return['success'] = true;
                $return['distance'] = [];

                foreach ($data as $item) {
                    array_push($return['distance'], $item['distance']['value']);
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
     * @return string
     */
    public function mountMatrixString($providers)
    {
        try {
            $matrixString = "";

            foreach ($providers as $item) {
                $matrixString .= "$item->latitude,$item->longitude|";
            }

            return $matrixString;
        } catch (\Throwable $th) {
            return "";
        }
    }

    public function getStaticMap(array $params)
    {
        $url = $this->static_map_url
            . "?key=" . $this->directions_key_api
            . '&size=' . $params['width'] . 'x' . $params['height'];

        if (array_key_exists('markers', $params)) {
            foreach ($params['markers'] as $marker) {
                $url .= "&markers=" . $marker;
            }
        }

        if (array_key_exists('path', $params)) {
            foreach ($params['path'] as $path) {
                $url .= "&path=" . $path;
            }
        }

        if (array_key_exists('center', $params))
            $url .= "&center=" . $params['center'];

        if (array_key_exists('scale', $params))
            $url .= "&scale=" . $params['scale'];

        if (array_key_exists('zoom', $params))
            $url .= '&zoom=' . $params['zoom'];
        else if (!array_key_exists('path', $params) && !array_key_exists('markers', $params))
            $url .= '&zoom=' . self::DEFAULT_ZOOM;//zoom is required only if markers neither path is provided

        return $url;
    }

    /**
     * Gets static map containing the route especified by paht parameter;
     *
     * @param array  $points points in the request's route
     * @param int  $with map width size
     * @param int  $height map height size
     *
     * @return String    url
     */
    public function getStaticMapByPath(array $points, int $width = 520, int $height = 520)
    {
        $path = "color:0xff0000ff|weight:5";

        foreach ($points as $point) $path .= "|" . $point;
        $markers = [
            "shadow:false|scale:2|icon:http://d1a3f4spazzrp4.cloudfront.net/receipt-new/marker-start@2x.png|" . $points[0],
            "shadow:false|scale:2|icon:http://d1a3f4spazzrp4.cloudfront.net/receipt-new/marker-finish@2x.png|" . $points[count($points) - 1],
        ];

        return self::getStaticMap([
            'path' => [$path],
            'width' => $width,
            'height' => $height,
            "markers" => $markers
        ]);
    }

}