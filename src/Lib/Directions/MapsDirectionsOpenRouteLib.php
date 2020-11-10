<?php

namespace Codificar\Geolocation\Lib;

//Internal Uses
use Codificar\Geolocation\Models\GeolocationSettings;
use Codificar\Geolocation\Helper;

    /**
     * Geolocation requests on OpenRoute Maps API
     */
    class MapsDirectionsOpenRouteLib implements IMapsDirections
    {

        /**
         * @var String  $url_api URL to access API
         */
        private $url_api;

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
        public function __construct($apiKey = null, $apiUrl = null)
        {
            $this->directions_key_api = $apiKey ? $apiKey : GeolocationSettings::getDirectionsKey();
            $this->url_api = $apiUrl ? $apiUrl :GeolocationSettings::getDirectionsUrl();
            self::$settings_dist = GeolocationSettings::getDefaultDistanceUnit();
            self::$unit_text = self::$settings_dist==1 ? trans('api.mile') : trans('api.km');
        }

        /**
         * Gets and calculate distance on OpenRoute Maps
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
            \Log::info("before open: ". date("d/m/Y H:i:s"));
            $curl_string = $this->url_api . "/directions/driving-car?api_key=" . $this->directions_key_api . "&start=" . $source_long . "," . $source_lat . "&end=" . $dest_long . "," . $dest_lat . "";
            $php_obj = self::curlCall($curl_string);
            $response_obj = json_decode($php_obj);
            \Log::info("after open: ". date("d/m/Y H:i:s"));

            if(isset($response_obj->features[0]->properties->segments[0]->distance))
            {
                $dist = convert_distance_format(self::$settings_dist, $response_obj->features[0]->properties->segments[0]->distance);

                return array('success' => true, 'data' => [ 'distance' => $dist ]);
            }
            else
            {
                return array('success' => false);
            }
        }

        /**
         * Gets and calculate distance and duration on OpenRoute Maps
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

            $curl_string = $this->url_api . "/directions/driving-car?api_key=" . $this->directions_key_api . "&start=" . $source_long . "," . $source_lat . "&end=" . $dest_long . "," . $dest_lat . "";
            $php_obj = self::curlCall($curl_string);
            $response_obj = json_decode($php_obj);

            if(isset($response_obj->features[0]->properties->segments[0]->distance) && isset($response_obj->features[0]->properties->segments[0]->duration))
            {
                $dist = convert_distance_format(self::$settings_dist, $response_obj->features[0]->properties->segments[0]->distance);
                $time_in_minutes = convert_to_minutes($response_obj->features[0]->properties->segments[0]->duration);

                $distance_text = number_format(convert_distance_format(self::$settings_dist, $response_obj->features[0]->properties->segments[0]->distance),1) . " " . self::$unit_text;
                $duration_text = ceil(convert_to_minutes($response_obj->features[0]->properties->segments[0]->duration)) . " " . trans("api.minutes");

                return array('success' => true, 'data' => [ 'distance' => $dist, 'time_in_minutes' => $time_in_minutes, 'distance_text' => $distance_text, 'duration_text' => $duration_text ]);
            }
            else
            {
                return array('success' => false);
            }
        }

        /**
         * Return intermediaries multiple points in the route using OpenRoute Maps
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

            $curl_string = $this->url_api . "/directions/driving-car?api_key=" . $this->directions_key_api . "&start=" . $source_long . "," . $source_lat . "&end=" . $dest_long . "," . $dest_lat . "";
            
            return self::polylineProcess($curl_string);
        }

        /**
         * Return intermediaries multiple points in the route using OpenRoute Maps
         *
         * @param String       $source_address          String not used in this API.
         * @param String       $destination_address     String not used in this API.
         * @param Decimal      $source_lat              Decimal that represents the starting latitude of the request.
         * @param Decimal      $source_long             Decimal that represents the starting longitude of the request.
         * @param Decimal      $dest_lat                Decimal that represents the destination latitude of the request.
         * @param Decimal      $dest_long               Decimal that represents the destination longitude of the request.
         *
         * @return Array       ['points' => [['lat','lng']['lat','lng']...],'distance_text','duration_text','distance_value','duration_value']
         */
        public function getPolylineAndEstimateByAddresses($source_address = false, $destination_address = false, $source_lat = false, $source_long = false, $dest_lat = false, $dest_long = false)
        {
            if (!($source_lat || $source_long || $dest_lat || $dest_long))
            {
                return false;
            }

            return $this->getPolylineAndEstimateByDirections($source_lat, $source_long, $dest_lat, $dest_long);
        }

        /**
         * Creates and call request by curl client
         *
         * @param String       $curl_string         URL called on curl request.
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
         * @param Double       $duration            Duration of request in seconds.
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

            return $duration_format . trans("api.minutes");
        }

        /**
         * Process curl response and return array with polyline and estimates.
         *
         * @param String      $curl_string         URL called by curl.
         *
         * @return Array      $polyline            Array with polyline and estimates.
         */
        private static function polylineProcess($curl_string)
        {
            try {
                $php_obj = self::curlCall($curl_string);
                $response_obj = json_decode($php_obj, true);

                $polyline = array('points' => array(0 => ['lat'=>'','lng'=>'']));
                if(isset($response_obj['features']) && count($response_obj['features'][0]['geometry']['coordinates']))
                {
                    
                    foreach($response_obj['features'][0]['geometry']['coordinates'] as $index => $point)
                    {
                        $polyline['points'][$index]['lat'] = $point[1];
                        $polyline['points'][$index]['lng'] = $point[0];
                    }   
                    $duration = isset($response_obj['features'][0]['properties']['segments'][0]['duration']) ? $response_obj['features'][0]['properties']['segments'][0]['duration'] : 0.0;
                   
                    $polyline['distance_text'] = number_format(convert_distance_format(self::$settings_dist, $response_obj['features'][0]['properties']['segments'][0]['distance']),1) . self::$unit_text;
                    $polyline['duration_text'] = self::formatTime($duration);
                    $polyline['distance_value'] = convert_distance_format(self::$settings_dist, $response_obj['features'][0]['properties']['segments'][0]['distance']);
                    $polyline['duration_value'] = convert_to_minutes($duration);
                }
                else
                {
                    return false;
                }

                return $polyline;
            } catch (Exception $e) {
                \Log::error($e->getMessage());
                return false;
            }
            
        }

        /**
         * Returns intermediaries points in the route between multiple locations using OpenRoute Maps
         *
         * @param String        $wayPoints         Array with mutiples decimals thats represent the latitude and longitude of the points in the route.
         *
         * @return Array        ['points' => [['lat','lng']['lat','lng']...],'distance_text','duration_text','distance_value','duration_value','partial_distances','partial_durations']
         */
        public function getPolylineAndEstimateWithWayPoints($wayPoints, $optimize = 0)
        {
            $waysFormatted = [];
            if (!$this->directions_key_api || (!is_string($wayPoints) || !is_array(json_decode($wayPoints, true))))
            {
                return false;
            }

            $ways = json_decode($wayPoints);
            $waysLen = count($ways);
            if($waysLen < 2){
                return false;
            }else{
                foreach($ways as $index => $way){
                    $waysFormatted[] = [$way[1],$way[0]];
                }
                $postFields = '{"coordinates":'. json_encode($waysFormatted) .'}';
            }

            $curl_string = $this->url_api . "/directions/driving-car";

            return $this->polylineProcessWithPoints($curl_string, 'post', $postFields);
        }

        /**
         * Process curl response and return array with polyline and estimates.
         *
         * @param String      $curl_string      URL called by curl.
         * @param String      $verb             Defines the request verb.
         * @param String      $postFields       Params to POST request.
         *
         * @return Array      $polyline         Array with polyline and estimates.
         */
        private function polylineProcessWithPoints($curl_string, $verb=null, $postFields=null)
        {
            $php_obj = $this->curlCall($curl_string, $verb, $postFields);
            $response_obj = json_decode($php_obj, true);

            $polyline = array('points' => array(0 => ['lat'=>'','lng'=>'']));
            if(isset($response_obj['features']) && count($response_obj['features'][0]['geometry']['coordinates']))
            {
                foreach($response_obj['features'][0]['geometry']['coordinates'] as $index => $point)
                {
                    $polyline['points'][$index]['lat'] = $point[1];
                    $polyline['points'][$index]['lng'] = $point[0];
                }

                $partialsAndTotals = $this->getPartialsAndTotals($response_obj['features'][0]);
                
            }
            else if(isset($response_obj['routes']) && isset($response_obj['routes'][0]['geometry']))
            {
                $array_resp['points'] = $response_obj['routes'][0]['geometry'];

                $needle = metaphone('points');

                // get polyline response
                $obj = $array_resp;

                // flatten array into single level array using 'dot' notation
                $obj_dot = array_dot($obj);
                // create empty array_resp
                $polyline = [];
                // iterate
                foreach( $obj_dot as $key => $val)
                {
                    // Calculate the metaphone key and compare with needle
                    $val =  strcmp( metaphone($key, strlen($needle)), $needle) === 0 
                            ? PolyUtil::decode($val) // if matched decode polyline
                            : $val;
                    array_set($polyline, $key, $val);
                }

                $partialsAndTotals = $this->getPartialsAndTotals($response_obj['routes'][0]);

            }
            else
            {
                return false;
            }

            $polyline['distance_text'] = number_format(convert_distance_format(self::$settings_dist, $partialsAndTotals['total_distance']),1) . self::$unit_text;
            $polyline['duration_text'] = self::formatTime($partialsAndTotals['total_duration']);
            $polyline['distance_value'] = convert_distance_format(self::$settings_dist, $partialsAndTotals['total_distance']);
            $polyline['duration_value'] = convert_to_minutes($partialsAndTotals['total_duration']);
            $polyline['partial_distances'] = $partialsAndTotals['partial_distances'];
            $polyline['partial_durations'] = $partialsAndTotals['partial_durations'];

            return $polyline;
        }

    }