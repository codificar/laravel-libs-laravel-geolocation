<?php

namespace Codificar\Geolocation\Lib;

//Internal Uses
use Codificar\Geolocation\Models\GeolocationSettings;
use Codificar\Geolocation\Helper;

    /**
     * Geolocation requests on Bing Maps API
     */
    class MapsDirectionsBingLib implements IMapsDirections{

        /**
         * @var String  $url_api URL to access API
         */
        private $url_api = "https://dev.virtualearth.net/REST/v1";

        /**
         * @var String  $directions_key_api Key of API authentication
         */
        private $directions_key_api;

        /**
         * @var String  $settings_dist Default distance unit
         */
        private $settings_dist;

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
            $this->settings_dist = GeolocationSettings::getDefaultDistanceUnit();
            self::$unit_text = $this->settings_dist==1 ? trans('geolocationTrans::geolocation.mile') : trans('geolocationTrans::geolocation.km') ;
        }

        /**
         * Gets and calculate distance on Bing Maps
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
            \Log::info("before bing: ". date("d/m/Y H:i:s"));
            $curl_string = $this->url_api . "/Routes/driving?key=". $this->directions_key_api ."&o=json&c=en-US&fi=true&wp.0=". $source_lat .",". $source_long ."&wp.1=". $dest_lat .",". $dest_long;
            $php_obj = self::curlCall($curl_string);
            $response_obj = json_decode($php_obj);
            \Log::info("after bing: ". date("d/m/Y H:i:s"));

            if($response_obj->statusCode && $response_obj->statusCode == 200)
            {
                $dist = convert_distance_format($this->settings_dist, $response_obj->resourceSets[0]->resources[0]->travelDistance*1000);

                return array('success' => true, 'data' => [ 'distance' => $dist ]);
            }
            else
            {
                return array('success' => false);
            }
        }

        /**
         * Gets and calculate distance and duration on Bing Maps
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

            $curl_string = $this->url_api . "/Routes/driving?key=". $this->directions_key_api ."&o=json&c=en-US&fi=true&wp.0=". $source_lat .",". $source_long ."&wp.1=". $dest_lat .",". $dest_long;
            $php_obj = self::curlCall($curl_string);
            $response_obj = json_decode($php_obj);

            if($response_obj->statusCode && $response_obj->statusCode == 200)
            {
                $dist = $response_obj->resourceSets[0]->resources[0]->travelDistance;
                $time_in_minutes = convert_to_minutes($response_obj->resourceSets[0]->resources[0]->travelDurationTraffic);

                $distance_text = number_format($response_obj->resourceSets[0]->resources[0]->travelDistance,1) . " " . self::$unit_text;
                $duration_text = number_format(ceil(convert_to_minutes($response_obj->resourceSets[0]->resources[0]->travelDurationTraffic)),0) . " " . trans('geolocationTrans::geolocation.minutes');

                return array('success' => true, 'data' => [ 'distance' => $dist, 'time_in_minutes' => $time_in_minutes, 'distance_text' => $distance_text, 'duration_text' => $duration_text ]);
            }
            else
            {
                return array('success' => false);
            }
        }

        /**
         * Return intermediaries multiple points in the route using Bing Maps
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

            $curl_string = $this->url_api . "/Routes/driving?key=". $this->directions_key_api ."&o=json&c=en-US&fi=true&wp.0=". $source_lat .",". $source_long ."&wp.1=". $dest_lat .",". $dest_long . "&routePathOutput=Points";

            return self::polylineProcess($curl_string);
        }

        /**
         * Return intermediaries multiple points in the route using Bing Maps
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

            $curl_string = $this->url_api . "/Routes/driving?key=". $this->directions_key_api ."&o=json&c=en-US&fi=true&wp.0=". urlencode($source_address) ."&wp.1=". urlencode($destination_address) . "&routePathOutput=Points";

            return self::polylineProcess($curl_string);
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
         * @param Double       $duration            Duration of request in minutes.
         *
         * @return String      $duration_format     Duration format in hours and minutes.
         */
        private static function formatTime($duration)
        {
            $duration = number_format(ceil(convert_to_minutes($duration)),0);
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

            return $duration_format . trans('geolocationTrans::geolocation.minutes');
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
            $php_obj = self::curlCall($curl_string);
            $response_obj = json_decode($php_obj, true);

            $polyline = array('points' => array(0 => ['lat'=>'','lng'=>'']));
            $position = 0;

            if($response_obj['statusCode'] && $response_obj['statusCode'] == 200)
            {
                foreach($response_obj['resourceSets'][0]['resources'][0]['routePath']['line']['coordinates'] as $index => $point)
                {
                    $polyline['points'][$index]['lat'] = $point[0];
                    $polyline['points'][$index]['lng'] = $point[1];
                }

                $polyline['distance_text'] = number_format($response_obj['resourceSets'][0]['resources'][0]['travelDistance'],1) . self::$unit_text;
                $polyline['duration_text'] = self::formatTime($response_obj['resourceSets'][0]['resources'][0]['travelDurationTraffic']);
                $polyline['distance_value'] = $response_obj['resourceSets'][0]['resources'][0]['travelDistance'];
                $polyline['duration_value'] = convert_to_minutes($response_obj['resourceSets'][0]['resources'][0]['travelDurationTraffic']);
            }
            else
            {
                return false;
            }

            return $polyline;
        }

        /**
         * Returns intermediaries points in the route between multiple locations using Bing Maps
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
                return false;
            }

            $ways = json_decode($wayPoints,true);
            $waysLen = count($ways);

            if($waysLen >= 2){
                foreach($ways as $index => $way){
                    $waysFormatted .= "&wp." . $index . "=". $way[0] . "," . $way[1];
                }
            }else if($waysLen < 2){
                return false;
            }

            $curl_string = $this->url_api . "/Routes/driving?key=". $this->directions_key_api ."&o=json&c=en-US&fi=true". $waysFormatted . "&routePathOutput=Points";

            return self::polylineProcessWithPoints($curl_string);
        }

        /**
         * Process curl response and return array with polyline and estimates.
         *
         * @param String      $curl_string         URL called by curl.
         *
         * @return Array      $polyline            Array with polyline and estimates.
         */
        private static function polylineProcessWithPoints($curl_string)
        {
            $php_obj = self::curlCall($curl_string);
            $response_obj = json_decode($php_obj, true);

            $polyline = array('points' => array(0 => ['lat'=>'','lng'=>'']));
            $position = 0;

            if($response_obj['statusCode'] && $response_obj['statusCode'] == 200)
            {
                foreach($response_obj['resourceSets'][0]['resources'][0]['routePath']['line']['coordinates'] as $index => $point)
                {
                    $polyline['points'][$index]['lat'] = $point[0];
                    $polyline['points'][$index]['lng'] = $point[1];
                }

                $partialDistances = [];
                $partialDurations = [];
                foreach($response_obj['resourceSets'][0]['resources'][0]['routeLegs'] as $index=>$leg)
                {
                    $partialDistances[$index] = number_format(($leg['travelDistance'] / 1000), 2);
                    $partialDurations[$index] = number_format(($leg['travelDuration'] / 60), 2);
                }

                $polyline['distance_text'] = number_format($response_obj['resourceSets'][0]['resources'][0]['travelDistance'],1) . self::$unit_text;
                $polyline['duration_text'] = self::formatTime($response_obj['resourceSets'][0]['resources'][0]['travelDurationTraffic']);
                $polyline['distance_value'] = $response_obj['resourceSets'][0]['resources'][0]['travelDistance'];
                $polyline['duration_value'] = convert_to_minutes($response_obj['resourceSets'][0]['resources'][0]['travelDurationTraffic']);
                $polyline['partial_distances'] = $partialDistances;
                $polyline['partial_durations'] = $partialDurations;
                $polyline['partial_durations'] = $partialDurations;
            }
            else
            {
                return false;
            }

            return $polyline;
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
                $curlString = $this->url_api . "/Routes/DistanceMatrix?key=" .
                    $this->directions_key_api .
                    "&origins=$sourceLat,$sourceLong" .
                    "&destinations=$destinations" .
                    "&travelMode=driving";
    
                $callApi = self::curlCall($curlString);
                $response = json_decode($callApi, true);
                
                $return = array('success' => false);
                
                if (is_array($response) && array_key_exists('statusCode', $response) && $response['statusCode'] == 200) {
                    $data = $response['resourceSets'][0]['resources'][0]['results'];
                    
                    $return['success'] = true;
                    $return['distance'] = [];

                    foreach ($data as $item) {
                        array_push($return['distance'], $item['travelDistance']);
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