<?php

namespace Codificar\Geolocation\Lib;

//Internal Uses
use Codificar\Geolocation\Models\GeolocationSettings;
use Codificar\Geolocation\Helper;

//External Uses
use GeometryLibrary\PolyUtil;

    /**
     * Geolocation requests o Flight Map API
     */
    class MapsDirectionsFlightMap implements IMapsDirections
    {

        /**
         * @var String  $url_api URL to access API
         */
        private $url_api = "https://maps.flightmap.io/api/";

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
            self::$settings_dist = GeolocationSettings::getDefaultDistanceUnit();
            self::$unit_text = self::$settings_dist==1 ? trans('api.mile') : trans('api.km');
        }

        /**
         * Gets and calculate distance on Flight Map
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
           
            $curl_string = $this->url_api . "/directions/json?origin=" . $source_lat . "," . $source_long . "&destination=" . $dest_lat . "," . $dest_long . "&key=" . $this->directions_key_api . "";
            $php_obj = self::curlCall($curl_string);
            $response_obj = json_decode($php_obj);
          

            if($response_obj->status && $response_obj->status == 'OK')
            {
                $dist = convert_distance_format(self::$settings_dist, $response_obj->routes[0]->legs[0]->distance->value);

                return array('success' => true, 'data' => [ 'distance' => $dist ]);
            }
            else
            {
                return array('success' => false);
            }
        }

        /**
         * Gets and calculate distance and duration on Flight Map
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

            
            $points = '[{"lat":"'.$source_lat.'","lng":"'.$source_long.'"},{"lat":"'.$dest_lat.'","lng":"'.$dest_long.'"}]';
            
            $params         =   array(
                "fm_token"       =>  $this->directions_key_api,
                "points"  =>  $points,
                "driving_mode"  =>  'car',
            );

            $curl_string    =   $this->url_api . "directions?" . http_build_query($params);
            $php_obj        =   self::curlCall($curl_string);
            $response_obj   =   json_decode($php_obj);
            
            if($response_obj->status == 200 && $response_obj->message == 'Successful')
            {                     
                $values = $this->formatDistanceTimeText($response_obj);

                return array('success' => true, 'data' => [ 'distance' => $values['convertDist'], 'time_in_minutes' => $values['convertTime'], 
                'distance_text' => $values['distance_text'], 'duration_text' => $values['duration_text'] ]);
            }
            else
            {
                return array('success' => false);
            }
        }       

        /**
         * Return intermediaries multiple points in the route using Flight Map
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

            $points = '[{"lat":"'.$source_lat.'","lng":"'.$source_long.'"},{"lat":"'.$dest_lat.'","lng":"'.$dest_long.'"}]';
            
            $params         =   array(
                "fm_token"       =>  $this->directions_key_api,
                "points"  =>  $points,
                "driving_mode"  =>  'car',
            );

            $curl_string    =   $this->url_api . "directions?" . http_build_query($params);
           
            return self::polylineProcess($curl_string);
        }

        /**
         * Return intermediaries multiple points in the route using Flight Map
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

            $points = '[{"lat":"'.$source_lat.'","lng":"'.$source_long.'"},{"lat":"'.$dest_lat.'","lng":"'.$dest_long.'"}]';
            
            $params         =   array(
                "fm_token"       =>  $this->directions_key_api,
                "points"  =>  $points,
                "driving_mode"  =>  'car',
            );

            $curl_string    =   $this->url_api . "directions?" . http_build_query($params);
           
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
            $response_obj = json_decode($php_obj);
            
            if($response_obj->status == 200 && $response_obj->message == 'Successful')
            {                            
                $originalPoints = $response_obj->data->paths[0]->points;
                $polyline['points'] = $originalPoints;

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
                $values = self::formatDistanceTimeText($response_obj);
                $array_resp['distance_text'] = $values['distance_text'];
                $array_resp['duration_text'] = $values['duration_text'];
                $array_resp['distance_value'] = $values['convertDist'];
                $array_resp['duration_value'] = $values['convertTime'];
            }
            else
            {
                return false;
            }
           
            return $array_resp;
        }

        private function formatDistanceTimeText($response_obj){
            $responseArray = array();
            $responseArray['originalTime'] = $response_obj->data->paths[0]->time;
            $responseArray['originalDistance'] = $response_obj->data->paths[0]->distance;    

            $responseArray['convertDist'] = self::convert_meters(self::$settings_dist, $responseArray['originalDistance']);
            $responseArray['convertTime'] = self::convert_to_miliseconds_to_minutes($responseArray['originalTime']);
            
            $responseArray['distance_text'] = number_format($responseArray['convertDist'], 1) . " " . self::$unit_text;
            $responseArray['duration_text'] = ceil($responseArray['convertTime']) . " " . trans("api.minutes");

            return $responseArray;
        }

        private function convert_meters($unit_dist, $response_dist){
            if (isset($response_dist)) {
                if ($unit_dist == 1) {
                    //Miles
                    $dist = $response_dist * 0.0006213712;
                } else {
                    //Km
                    $dist = $response_dist* 0.001;
                }
            } else {
                $dist = 0;
            }
        
            return $dist;
        }

        private function convert_to_miliseconds_to_minutes($response_time){
            if (isset($response_time))
                $time_in_Minutes = ($response_time / 60000);
            else
                $time_in_Minutes = 0;
        
            return $time_in_Minutes;
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
            $arrayPoints = json_decode($wayPoints);
            $waysFormatted = [];
            foreach ($arrayPoints as $key => $value) {
                $jsonPoints = (object) array(
                    "lat" => strval($value[0]),
                    "lng" => strval($value[1])
                );
                array_push($waysFormatted, $jsonPoints);
            }
            $params         =   array(
                "fm_token"       =>  $this->directions_key_api,
                "points"         =>  json_encode($waysFormatted),
                "driving_mode"   =>  'car'
            );

            $curl_string    =   $this->url_api . "directions?" . http_build_query($params);
            $php_obj        =   self::curlCall($curl_string);
            $response_obj   =   json_decode($php_obj);
           
            if($response_obj->status == 200 && $response_obj->message == 'Successful' && isset($response_obj->data->paths[0])) { 
                return self::polylineProcessWithPoints($response_obj->data->paths[0], $response_obj);
            }else {
                return false;
            }            
        }

        /**
         * Process curl response and return array with polyline and estimates.
         *
         * @param String      $curl_string         URL called by curl.
         *
         * @return Array      $polyline            Array with polyline and estimates.
         */
        private static function polylineProcessWithPoints($values, $infoValue) {
            $decodedPolyline = self::polylineDecodeWithFlight($values->points);            
            $distanceTimeValue = self::formatDistanceTimeText($infoValue);
            
            if(isset($decodedPolyline['points']) && isset($distanceTimeValue)) {
                $array_resp['waypoint_order'] = [];
                $array_resp['points'] = $decodedPolyline['points'];    
    
                $array_resp['distance_text'] = $distanceTimeValue['distance_text'];
                $array_resp['duration_text'] = $distanceTimeValue['duration_text'];
                $array_resp['distance_value'] = round($distanceTimeValue['convertDist'], 2);
                $array_resp['duration_value'] = round($distanceTimeValue['convertTime'], 2 );
    
               
                $partialDistances = number_format(($distanceTimeValue['convertDist'] / 2), 2);
                $partialDurations = number_format(($distanceTimeValue['convertTime'] / 2), 2);

                $partialDistances = [];
                $partialDurations = [];   

                foreach ($values->legs as $key => $value) {
                    $partialDistances[$key] = number_format(($value->distance / 1000), 2);
                    $partialDurations[$key] = number_format(($value->time / 60), 2);
                }

                $array_resp['partial_distances'] = $partialDistances;
                $array_resp['partial_durations'] = $partialDurations;
                
                return $array_resp;
            }           
        }

        private static function polylineDecodeWithFlight($originalPoints){
            $polyline['points'] = $originalPoints;
            $needle = metaphone('points');
            $obj = $polyline;
            $obj_dot = array_dot($obj);
            $array_resp = [];
            foreach( $obj_dot as $key => $val)
            {
                $val =  strcmp( metaphone($key, strlen($needle)), $needle) === 0 
                        ? PolyUtil::decode($val) // if matched decode polyline
                        : $val;
                array_set($array_resp, $key, $val);
            }
            return $array_resp;
        }

        /**
         * Get the matrix distance in providers list
         * 
         * @param Array        $providers             Array of providers.
         * @param Decimal      $sourceLat             Decimal that represents the starting latitude of the request.
         * @param Decimal      $sourceLong            Decimal that represents the starting longitude of the request.
         * 
         * @return Array 
         */
        public function getMatrixDistance($providers, $sourceLat, $sourceLong)
        {
            try {
                $destinations = $this->mountMatrixString($providers);
                $curlString = $this->url_api . "matrix?fm_token=$this->directions_key_api" .
                    "&start=$sourceLat,$sourceLong" . 
                    "&end=$destinations";
                
                $callApi = self::curlCall($curlString);
                $response = json_decode($callApi, true);
                
                $return = array('success' => false);

                if (is_array($response) && array_key_exists('routes', $response)) {
                    $data = $response['routes'];
                    $return['success'] = true;
                    $return['distance'] = [];

                    foreach ($data as $item) {
                        array_push($return['distance'], $item['elements'][0]['distance']);
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
                    $matrixString .= "$item->latitude,$item->longitude;";
                }

                if (strlen($matrixString))
                    $matrixString = substr($matrixString, 0, -1);

                return $matrixString;
            } catch (\Throwable $th) {
                return "";
            }
        }

    }