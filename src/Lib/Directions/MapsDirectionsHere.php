<?php

namespace Codificar\Geolocation\Lib\Directions;

//Internal Uses
use Codificar\Geolocation\Models\GeolocationSettings;
use Codificar\Geolocation\Helper;
use Codificar\Geolocation\Utils\Polyline\FlexiblePolyline;

//External Uses
use GeometryLibrary\PolyUtil;

/**
 * Geolocation requests o Here Map API
 */
class MapsDirectionsHere implements IMapsDirections
{
    /**
     * @var String $url_api URL to access Routes API
     */
    private $url_api = "https://router.hereapi.com/v8/";

    /**
     * @var String $matrix_url_api URL to access Matrix API
     */
    private $matrix_url_api = "https://matrix.router.hereapi.com/v8/";

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
        self::$settings_dist = GeolocationSettings::getDefaultDistanceUnit();
        self::$unit_text = self::$settings_dist == 1 ? trans('geolocationTrans::geolocation.mile') : trans('geolocationTrans::geolocation.km');
    }

    /**
     * Gets and calculate distance on Here Map
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

        $curl_string = $this->url_api . "/directions/json?origin=" . $source_lat . "," . $source_long . "&destination=" . $dest_lat . "," . $dest_long . "&key=" . $this->directions_key_api . "";
        $php_obj = self::curlCall($curl_string);
        $response_obj = json_decode($php_obj);


        if ($response_obj->status && $response_obj->status == 'OK') {
            $dist = convert_distance_format(self::$settings_dist, $response_obj->routes[0]->legs[0]->distance->value);

            return array('success' => true, 'data' => ['distance' => $dist]);
        } else {
            return array('success' => false);
        }
    }

    /**
     * Gets and calculate distance and duration on Here Map
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

        $params = array(
            "apiKey" => "$this->directions_key_api",
            "transportMode" => "car",
            "return" => "summary",
            "origin" => $source_lat . "," . $source_long,
            "destination" => $dest_lat . "," . $dest_long,
        );

        $curl_string = $this->url_api . "routes?" . http_build_query($params);
        $php_obj = self::curlCall($curl_string);
        $response_obj = json_decode($php_obj);

        if (isset($response_obj->routes[0])) {
            $values = self::formatDistanceTimeText($response_obj);

            return array('success' => true, 'data' => ['distance' => $values['convertDist'], 'time_in_minutes' => $values['convertTime'],
                'distance_text' => $values['distance_text'], 'duration_text' => $values['duration_text']]);
        } else {
            return array('success' => false);
        }
    }

    /**
     * Return intermediaries multiple points in the route using Here Map
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
            return array('success' => false);
        }

        $params = array(
            "apiKey" => "$this->directions_key_api",
            "transportMode" => "car",
            "return" => "polyline,summary",
            "origin" => $source_lat . "," . $source_long,
            "destination" => $dest_lat . "," . $dest_long,
        );

        $curl_string = $this->url_api . "routes?" . http_build_query($params);

        return self::polylineProcess($curl_string);
    }

    /**
     * Return intermediaries multiple points in the route using Here Map
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
            return array('success' => false);
        }

        $params = array(
            "apiKey" => "$this->directions_key_api",
            "transportMode" => "car",
            "return" => "polyline,summary",
            "origin" => $source_lat . "," . $source_long,
            "destination" => $dest_lat . "," . $dest_long,
        );

        $curl_string = $this->url_api . "routes?" . http_build_query($params);

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
        $response_obj = json_decode($php_obj);

        if (isset($response_obj->routes[0])) {
            $responsePolyline = $response_obj->routes[0]->sections[0]->polyline;

            $array_resp = [];
            $values = self::formatDistanceTimeText($response_obj);
            $array_resp['points'] = self::decodePolyline($responsePolyline);
            $array_resp['distance_text'] = $values['distance_text'];
            $array_resp['duration_text'] = $values['duration_text'];
            $array_resp['distance_value'] = $values['convertDist'];
            $array_resp['duration_value'] = $values['convertTime'];
        } else {
            return false;
        }
        return $array_resp;
    }

    private static function formatDistanceTimeText($response_obj)
    {
        $sumarry = $response_obj->routes[0]->sections[0]->summary;

        $responseArray = array();
        $responseArray['originalTime'] = $sumarry->duration / 60;
        $responseArray['originalDistance'] = $sumarry->length;

        $responseArray['convertDist'] = self::convert_meters(self::$settings_dist, $responseArray['originalDistance']);
        $responseArray['convertTime'] = $responseArray['originalTime'];

        $responseArray['distance_text'] = number_format($responseArray['convertDist'], 1) . " " . self::$unit_text;
        $responseArray['duration_text'] = ceil($responseArray['convertTime']) . " " . trans('geolocationTrans::geolocation.minutes');

        return $responseArray;
    }

    private static function convert_meters($unit_dist, $response_dist)
    {
        if (isset($response_dist)) {
            if ($unit_dist == 1) {
                //Miles
                $dist = $response_dist * 0.0006213712;
            } else {
                //Km
                $dist = $response_dist * 0.001;
            }
        } else {
            $dist = 0;
        }

        return $dist;
    }

    private function convert_to_miliseconds_to_minutes($response_time)
    {
        if (isset($response_time)) {
            $time_in_Minutes = ($response_time / 60000);
        } else {
            $time_in_Minutes = 0;
        }

        return $time_in_Minutes;
    }


    /**
     * Returns intermediaries points in the route between multiple locations using OpenRoute Maps
     *
     * @param String $wayPoints Array with mutiples decimals thats represent the latitude and longitude of the points in the route.
     * @param $shortestDistance
     * @return Array        ['points' => [['lat','lng']['lat','lng']...],'distance_text','duration_text','distance_value','duration_value','partial_distances','partial_durations']
     */
    public function getPolylineAndEstimateWithWayPoints($wayPoints, $optimize = 0, $shortestDistance = null)
    {
        $ways = json_decode($wayPoints, true);
        $waysLen = count($ways);
        if (!$this->directions_key_api || !isset($ways[0]) || !isset($ways[1]) || $waysLen < 2) {
            return array('success' => false);
        }

        $lay_key = array_key_last($ways);
        $origin = $ways[0][0] . "," . $ways[0][1];
        $destination = $ways[$lay_key][0] . "," . $ways[$lay_key][1];

        $via = false;
        foreach ($ways as $key => $value) {
            if ($key != 0 && $key != $lay_key) {
                $via .= "&via=" . $value[0] . "," . $value[1];
            }
        }

        $params = array(
            "apiKey" => $this->directions_key_api,
            "transportMode" => "car",
            "return" => "polyline,summary",
            "origin" => $origin,
            "destination" => $destination,
        );
        if ($shortestDistance) {
            $params["routingMode"] = "short";
        }

        $curl_string = $this->url_api . "routes?" . http_build_query($params);
        $via ? $curl_string = $curl_string . $via : null;

        return self::polylineProcessWithPoints($curl_string);
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
        $php_obj = self::curlCall($curl_string);
        $response_obj = json_decode($php_obj);
        if (isset($response_obj->routes[0])) {
            return self::formatDistanceTimeTextWithPoints($response_obj);
        } else {
            return false;
        }
    }

    private static function formatDistanceTimeTextWithPoints($response_obj)
    {
        $routes = $response_obj->routes[0]->sections;
        $responseArray = array();
        $totalDistance = 0;
        $totalDuration = 0;

        $points = [];

        foreach ($routes as $key => $value) {
            $points[$key] = self::decodePolylineToObject($value->polyline);
            $originalTime = number_format(($value->summary->duration / 60));
            $originalDistance = $value->summary->length;

            $convertDist = self::convert_meters(self::$settings_dist, $originalDistance);
            $convertTime = $originalTime;

            $partialDistances[$key] = (string)$convertDist;
            $partialDurations[$key] = (string)$convertTime;

            $totalDistance += $convertDist;
            $totalDuration += $convertTime;
        }

        $pointsArray = [];

        foreach ($points as $point) {
            foreach ($point as $p) {
                array_push($pointsArray, $p);
            }
        }

        $responseArray['waypoint_order'] = [];
        $responseArray['points'] = $pointsArray;
        $responseArray['partial_distances'] = $partialDistances;
        $responseArray['partial_durations'] = $partialDurations;

        $responseArray['distance_value'] = $totalDistance;
        $responseArray['duration_value'] = $totalDuration;

        $responseArray['distance_text'] = number_format($totalDistance, 1) . " " . self::$unit_text;
        $responseArray['duration_text'] = ceil($totalDuration) . " " . trans('geolocationTrans::geolocation.minutes');

        return $responseArray;
    }

    private static function decodePolyline($points)
    {
        return FlexiblePolyline::decode($points)['polyline'];
    }

    private static function decodePolylineToObject($points)
    {
        return FlexiblePolyline::decodeToObject($points)['polyline'];
    }

    private static function curlCallJson($curl_string, $payload)
    {
        $session = curl_init($curl_string);
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($session, CURLOPT_POST, true);
        curl_setopt($session, CURLOPT_POSTFIELDS, $payload);
        $msg_chk = curl_exec($session);

        return $msg_chk;
    }

    /**
     * Gets and calculate distance and duration on Here Map
     *
     * @param Decimal $source_lat Decimal that represents the starting latitude of the request.
     * @param Decimal $source_long Decimal that represents the starting longitude of the request.
     * @param Decimal $dest_lat Decimal that represents the destination latitude of the request.
     * @param Decimal $dest_long Decimal that represents the destination longitude of the request.
     *
     * @return Array        ['success', 'data' => ['distance','time_in_minutes','distance_text','duration_text']]
     */
    public function getMatrixDistance($providers, $sourceLat, $sourceLong)
    {
        try {
            if (!$this->directions_key_api) {
                return array('success' => false);
            }

            $destinations = $this->mountDestination($providers);
            $params = array(
                "origins" => [["lat" => $sourceLat, "lng" => $sourceLong]],
                "destinations" => $destinations,
                "regionDefinition" => ["type" => "world"],
                "matrixAttributes" => ["travelTimes", "distances"]
            );

            $payload = json_encode($params);

            $curl_string = $this->matrix_url_api . "matrix?async=false&apiKey=$this->directions_key_api";
            $php_obj = self::curlCallJson($curl_string, $payload);
            $response = json_decode($php_obj);

            $return = array('success' => false);

            if (is_object($response)) {
                $return['success'] = true;
                $return['distance'] = $response->matrix->distances;
            }

            return $return;
        } catch (\Throwable $th) {
            \Log::error($th->getMessage().$th->getTraceAsString());
            return array('success' => false);
        }
    }

    /**
     * Mount destinations string for matrix
     *
     * @param array $providers
     * @return array
     */
    public function mountDestination($providers)
    {
        try {
            $destinations = [];

            foreach ($providers as $item) {
                array_push($destinations, ["lat" => $item->latitude, "lng" => $item->longitude]);
            }

            return $destinations;
        } catch (\Throwable $th) {
            return [];
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
        $this->directions_key_api = "ug7Vhp_6fapXqkurR9hs6zjCo3EgqV77ZlKqWmpRy0Q";
        $url = "https://image.maps.ls.hereapi.com/mia/1.6/route" .
            "?apiKey=" . $this->directions_key_api
            . '&w=' . $width
            . '&h=' . $height
            . '&m0=' . $points[0]
            . '&m1=' . $points[count($points) - 1]
            . '&mfc0=00ff00'//colo initial point marker
            . '&mfc1=ff0000'//color last point marker
            . '&mtxc=000000'//color last point marker
            . '&sc0=ff0000ff'// path color
            . '&mlbl=0'// path color
            . '&r0=';

        foreach ($points as $point) {
            $url .= $point . ',';
        }

        return $url;
    }
}
