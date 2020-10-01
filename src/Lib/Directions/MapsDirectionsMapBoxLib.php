<?php

namespace Codificar\Geocode\Lib;

//Internal Uses
use Codificar\GatewayNfe\Models\GeolocationSettings;
use Codificar\GatewayNfe\Helper;

    /**
     * Geolocation requests on MapBox Maps API
     */
    class MapsDirectionsMapBoxLib implements IMapsDirections
    {

        /**
         * @var String  $url_api URL to access API
         */
        private $url_api = "https://api.mapbox.com/directions/v5";

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
         * Gets and calculate distance on MapBox Maps
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
            \Log::info("before box: ". date("d/m/Y H:i:s"));
            $curl_string = $this->url_api . "/mapbox/driving-traffic/" . $source_long . "," . $source_lat .";" . $dest_long . "," . $dest_lat . "?access_token=" . $this->directions_key_api;
            $php_obj = self::curlCall($curl_string);
            $response_obj = json_decode($php_obj);
            \Log::info("after box: ". date("d/m/Y H:i:s"));

            if(isset($response_obj->code) && $response_obj->code == 'Ok')
            {
                $dist = convert_distance_format(self::$settings_dist, $response_obj->routes[0]->legs[0]->distance);

                return array('success' => true, 'data' => [ 'distance' => $dist ]);
            }
            else
            {
                return array('success' => false);
            }
        }

        /**
         * Gets and calculate distance and duration on MapBox Maps
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

            $curl_string = $this->url_api . "/mapbox/driving-traffic/" . $source_long . "," . $source_lat .";" . $dest_long . "," . $dest_lat . "?access_token=" . $this->directions_key_api ."";
            $php_obj = self::curlCall($curl_string);
            $response_obj = json_decode($php_obj);

            if(isset($response_obj->code) && $response_obj->code == 'Ok')
            {
                $dist = convert_distance_format(self::$settings_dist, $response_obj->routes[0]->legs[0]->distance);
                $time_in_minutes = convert_to_minutes($response_obj->routes[0]->legs[0]->duration);

                $distance_text = number_format(convert_distance_format(self::$settings_dist, $response_obj->routes[0]->legs[0]->distance),1) . " " . self::$unit_text;
                $duration_text = ceil(convert_to_minutes($response_obj->routes[0]->legs[0]->duration)) . " " . trans("api.minutes");

                return array('success' => true, 'data' => [ 'distance' => $dist, 'time_in_minutes' => $time_in_minutes, 'distance_text' => $distance_text, 'duration_text' => $duration_text ]);
            }
            else
            {
                return array('success' => false);
            }
        }

        /**
         * Return intermediaries multiple points in the route using MapBox Maps
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

            $curl_string = $this->url_api . "/mapbox/driving-traffic/" . $source_long . "," . $source_lat .";" . $dest_long . "," . $dest_lat . "?access_token=" . $this->directions_key_api . "&steps=true";

            return self::polylineProcess($curl_string);
        }

        /**
         * Return intermediaries multiple points in the route using MapBox Maps
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
            $php_obj = self::curlCall($curl_string);
            $response_obj = json_decode($php_obj, true);

            $polyline = array('points' => array(0 => ['lat'=>'','lng'=>'']));
            $index = 0;

            if(array_key_exists('code', $response_obj) && $response_obj['code'] == 'Ok')
            {
                foreach($response_obj['routes'][0]['legs'][0]['steps'] as $steps)
                {
                    foreach($steps['intersections'] as $intersections)
                    {
                        $polyline['points'][$index]['lat'] = $intersections['location'][1];
                        $polyline['points'][$index]['lng'] = $intersections['location'][0];
                        $index ++;
                    }
                }

                $polyline['distance_text'] = number_format(convert_distance_format(self::$settings_dist, $response_obj['routes'][0]['legs'][0]['distance']),1) . self::$unit_text;
                $polyline['duration_text'] = self::formatTime($response_obj['routes'][0]['legs'][0]['duration']);
                $polyline['distance_value'] = convert_distance_format(self::$settings_dist, $response_obj['routes'][0]['legs'][0]['distance']);
                $polyline['duration_value'] = convert_to_minutes($response_obj['routes'][0]['legs'][0]['duration']);
            }
            else
            {
                return false;
            }

            return $polyline;
        }

    }