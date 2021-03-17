<?php

namespace Codificar\Geolocation\Lib;

//Internal Uses
use Codificar\Geolocation\Models\GeolocationSettings;
use Codificar\Geolocation\Helper;

//External Uses
use GeometryLibrary\PolyUtil;

    /**
     * Geolocation requests on Google Maps API
     */
    class MapsDirectionsGoogleLib implements IMapsDirections
    {

        /**
         * @var String  $url_api URL to access API
         */
        private $url_api = "https://maps.googleapis.com/maps/api";

        /**
         * @var String  $directions_key_api Key of API authentication
         */
        private $directions_key_api;

        /**
         * @var String  $settings_dist Default distance unit
         */
        private static $settings_dist;

        /**
         * @var String  $unit_text Distance unit text
         */
        private static $unit_text;

        /**
         * Defined properties
         */
        public function __construct($apiKey = null)
        {
            $this->directions_key_api = $apiKey ? $apiKey : GeolocationSettings::getDirectionsKey();
            self::$settings_dist = 0;
            self::$unit_text = trans('api.km');
        }

        /**
         * Gets and calculate distance on Google Maps
         *
         * @param Decimal       $source_lat         Decimal that represents the starting latitude of the request.
         * @param Decimal       $source_long        Decimal that represents the starting longitude of the request.
         * @param Decimal       $dest_lat           Decimal that represents the destination latitude of the request.
         * @param Decimal       $dest_long          Decimal that represents the destination longitude of the request.
         *
         * @return Array        ['success', 'data' => ['distance']]
         */
        public function getDistanceByDirections($source_lat, $source_long, $dest_lat, $dest_long)
        {
            if (!$this->directions_key_api)
            {
                return array('success' => false);
            }
            \Log::info("before google: ". date("d/m/Y H:i:s"));
            $curl_string = $this->url_api . "/directions/json?origin=" . $source_lat . "," . $source_long . "&destination=" . $dest_lat . "," . $dest_long . "&key=" . $this->directions_key_api . "";
            $php_obj = self::curlCall($curl_string);
            $response_obj = json_decode($php_obj);
            \Log::info("after google: ". date("d/m/Y H:i:s"));

            if($response_obj->status && $response_obj->status == 'OK')
            {
                $dist = convert_distance_format(0, $response_obj->routes[0]->legs[0]->distance->value);

                return array('success' => true, 'data' => [ 'distance' => $dist ]);
            }
            else
            {
                return array('success' => false);
            }
        }

        /**
         * Gets and calculate distance and duration on Google Maps
         *
         * @param Decimal       $source_lat         Decimal that represents the starting latitude of the request.
         * @param Decimal       $source_long        Decimal that represents the starting longitude of the request.
         * @param Decimal       $dest_lat           Decimal that represents the destination latitude of the request.
         * @param Decimal       $dest_long          Decimal that represents the destination longitude of the request.
         *
         * @return Array        ['success', 'data' => ['distance','time_in_minutes','distance_text','duration_text']]
         */
        public function getDistanceAndTimeByDirections($source_lat, $source_long, $dest_lat, $dest_long)
        {
            if (!$this->directions_key_api)
            {
                return array('success' => false);
            }

            $curl_string = $this->url_api . "/directions/json?origin=" . $source_lat . "," . $source_long . "&destination=" . $dest_lat . "," . $dest_long . "&key=" . $this->directions_key_api . "";
            $php_obj = self::curlCall($curl_string);
            $response_obj = json_decode($php_obj);

            if($response_obj->status && $response_obj->status == 'OK')
            {
                $dist = convert_distance_format(0, $response_obj->routes[0]->legs[0]->distance->value);
                $time_in_minutes = convert_to_minutes($response_obj->routes[0]->legs[0]->duration->value);

                $distance_text = number_format(convert_distance_format(0, $response_obj->routes[0]->legs[0]->distance->value),1) . " " . self::$unit_text;
                $duration_text = ceil(convert_to_minutes($response_obj->routes[0]->legs[0]->duration->value)) . " " . trans("api.minutes");

                return array('success' => true, 'data' => [ 'distance' => $dist, 'time_in_minutes' => $time_in_minutes, 'distance_text' => $distance_text, 'duration_text' => $duration_text ]);
            }
            else
            {
                return array('success' => false);
            }
        }

        /**
         * Return intermediaries multiple points in the route using Google Maps
         *
         * @param Decimal       $source_lat         Decimal that represents the starting latitude of the request.
         * @param Decimal       $source_long        Decimal that represents the starting longitude of the request.
         * @param Decimal       $dest_lat           Decimal that represents the destination latitude of the request.
         * @param Decimal       $dest_long          Decimal that represents the destination longitude of the request.
         *
         * @return Array        ['points' => [['lat','lng']['lat','lng']...],'distance_text','duration_text','distance_value','duration_value']
         */
        public function getPolylineAndEstimateByDirections($source_lat, $source_long, $dest_lat, $dest_long)
        {
            if (!$this->directions_key_api)
            {
                return false;
            }

            $curl_string = $this->url_api . "/directions/json?origin=" . $source_lat . "," . $source_long . "&destination=" . $dest_lat . "," . $dest_long . "&key=" . $this->directions_key_api . "";

            return self::polylineProcess($curl_string);
        }

        /**
         * Return intermediaries multiple points in the route using Google Maps
         *
         * @param String       $source_address         String that represents the starting address of the request.
         * @param String       $destination_address    String that represents the destination address of the request.
         * @param Decimal      $source_lat             Decimal that represents the starting latitude of the request.
         * @param Decimal      $source_long            Decimal that represents the starting longitude of the request.
         * @param Decimal      $dest_lat               Decimal that represents the destination latitude of the request.
         * @param Decimal      $dest_long              Decimal that represents the destination longitude of the request.
         *
         * @return Array       ['points' => [['lat','lng']['lat','lng']...],'distance_text','duration_text','distance_value','duration_value']
         */
        public function getPolylineAndEstimateByAddresses($source_address, $destination_address, $source_lat = false, $source_long = false, $dest_lat = false, $dest_long = false)
        {
            if (!$this->directions_key_api)
            {
                return false;
            }

            $curl_string = $this->url_api . "/directions/json?origin=" . urlencode($source_address) . "&destination=" . urlencode($destination_address) . "&key=" . $this->directions_key_api . "";

            return self::polylineProcess($curl_string);
        }

        /**
         * Creates and call request by curl client
         *
         * @param String       $curl_string         URL called on curl request.
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
         * @param Double       $duration            Duration of request in minutes.
         *
         * @return String      $duration_format     Duration format in hours and minutes.
         */
        private static function formatTime($duration)
        {
            $duration = ceil(convert_to_minutes($duration));
            if ($duration > 60)
            {
                $hours_full = $duration/60;
                $minutes_full = $hours_full-floor($hours_full);
                $minutes_float = (($minutes_full*100)*60)/100;
                $minutes = ceil($minutes_float);

                $duration_format = floor($hours_full) . "h" . $minutes;
            }
            else
            {
                $duration_format = $duration;
            }

            return $duration_format . ' ' . trans("api.minutes");
        }

        /**
         * Process curl response and return array with polyline and estimates.
         *
         * @param String      $curl_string         URL called by curl.
         *
         * @return Array      $array_resp          Array with polyline and estimates.
         */
        private static function polylineProcess($curl_string)
        {
            $php_obj = self::curlCall($curl_string);
            $response_obj = json_decode($php_obj, true);
            
            if($response_obj['status'] && $response_obj['status'] == 'OK')
            {
                $polyline['points'] = $response_obj['routes'][0]['overview_polyline']['points'];

                $needle = metaphone('points');

                // get polyline response
                $obj = $polyline;

                // flatten array into single level array using 'dot' notation
                $obj_dot = array_dot($obj);
                // create empty array_resp
                $array_resp = [];
                // iterate
                foreach( $obj_dot as $key => $val)
                {
                    // Calculate the metaphone key and compare with needle
                    $val =  strcmp( metaphone($key, strlen($needle)), $needle) === 0 
                            ? PolyUtil::decode($val) // if matched decode polyline
                            : $val;
                    array_set($array_resp, $key, $val);
                }

                $array_resp['distance_text'] = number_format(convert_distance_format(0, $response_obj['routes'][0]['legs'][0]['distance']['value']),1) . ' ' . self::$unit_text;
                $array_resp['duration_text'] = self::formatTime($response_obj['routes'][0]['legs'][0]['duration']['value']);
                $array_resp['distance_value'] = convert_distance_format(0, $response_obj['routes'][0]['legs'][0]['distance']['value']);
                $array_resp['duration_value'] = convert_to_minutes($response_obj['routes'][0]['legs'][0]['duration']['value']);
            }
            else
            {
                return false;
            }

            return $array_resp;
        }

         /**
         * Returns intermediaries points in the route between multiple locations using Google Maps
         *
         * @param String        $wayPoints         Array with mutiples decimals thats represent the latitude and longitude of the points in the route.
         *
         * @return Array        ['points' => [['lat','lng']['lat','lng']...],'distance_text','duration_text','distance_value','duration_value','partial_distances','partial_durations']
         */
        public function getPolylineAndEstimateWithWayPoints($wayPoints, $optimize = 0)
        {

            $waysFormatted = '';
            if (!$this->directions_key_api || (!is_string($wayPoints) || !is_array(json_decode($wayPoints, true))))
            {
                \Log::info("getPolylineAndEstimateWithWayPoints:false");
                return false;
            }

            $ways = json_decode($wayPoints);
            $waysLen = count($ways);
            if($optimize == 1) {
                $optimizeRoute = "optimize:true|";
            } else {
                $optimizeRoute = "optimize:false|";
            }

            if($waysLen > 2){
                foreach($ways as $index => $way){
                    if($index != 0 && $index < ($waysLen-1))
                        $waysFormatted .= !$waysFormatted ? ("&waypoints=" . $optimizeRoute . $way[0] . "," . $way[1]) : "|" . $way[0] . "," . $way[1];
                }

                $waysFormatted = rtrim($waysFormatted, "|");
            }else if($waysLen < 2){
                return false;
            }
            //Se tem otimizacao, usa a chave especifica pra otimizacao, senao, utiliza a chave normalmente
            if($optimize == 1) {
                $google_key = GeolocationSettings::getDirectionsGoogleOptimizeRoute();
            } else {
                $google_key = $this->directions_key_api;
            }
            $curl_string = $this->url_api . "/directions/json?key=" . $google_key . "&origin=" . urlencode($ways[0][0].",".$ways[0][1]) . "&destination=" . urlencode($ways[$waysLen-1][0].",".$ways[$waysLen-1][1]) . $waysFormatted;
           
            return self::polylineProcessWithPoints($curl_string);
        }

         /**
         * Process curl response and return array with polyline and estimates.
         *
         * @param String      $curl_string         URL called by curl.
         *
         * @return Array      $array_resp          Array with polyline and estimates.
         */
        private static function polylineProcessWithPoints($curl_string)
        {
            $php_obj = self::curlCall($curl_string);
            $response_obj = json_decode($php_obj, true);

            if($response_obj['status'] && $response_obj['status'] == 'OK')
            {
                $polyline['points'] = $response_obj['routes'][0]['overview_polyline']['points'];

                // Get the waypoint order, (needs if has optimize route)
                $waypoint_order = $response_obj['routes'][0]['waypoint_order'];

                $needle = metaphone('points');

                // get polyline response
                $obj = $polyline;

                // flatten array into single level array using 'dot' notation
                $obj_dot = array_dot($obj);
                // create empty array_resp
                $array_resp = [];
                // iterate
                foreach( $obj_dot as $key => $val)
                {
                    // Calculate the metaphone key and compare with needle
                    $val =  strcmp( metaphone($key, strlen($needle)), $needle) === 0 
                            ? PolyUtil::decode($val) // if matched decode polyline
                            : $val;
                    array_set($array_resp, $key, $val);
                }

                $partialDistances = [];
                $partialDurations = [];
                $totalDistance = 0;
                $totalDuration = 0;

                foreach($response_obj['routes'][0]['legs'] as $index=>$leg)
                {
                    $partialDistances[$index] = number_format(($leg['distance']['value'] / 1000), 2);
                    $partialDurations[$index] = number_format(($leg['duration']['value'] / 60), 2);

                    $totalDistance += $leg['distance']['value'];
                    $totalDuration += $leg['duration']['value'];
                }

                $array_resp['distance_text'] = number_format(convert_distance_format(0, $totalDistance),2) . self::$unit_text;
                $array_resp['duration_text'] = self::formatTime($totalDuration);
                $array_resp['distance_value'] = convert_distance_format(0, $totalDistance);
                $array_resp['duration_value'] = convert_to_minutes($totalDuration);
                $array_resp['partial_distances'] = $partialDistances;
                $array_resp['partial_durations'] = $partialDurations;
                $array_resp['waypoint_order'] = $waypoint_order;
                $array_resp['waypoint_order'] = [];
            }
            else
            {
                return false;
            }

            return $array_resp;
        }

    }