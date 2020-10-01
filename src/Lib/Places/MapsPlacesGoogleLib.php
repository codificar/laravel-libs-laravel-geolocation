<?php

namespace Codificar\Geocode\Lib;

//Internal Uses
use Codificar\GatewayNfe\Models\GeolocationSettings;

    /**
     * Geolocation requests on Google Maps API
     */
    class MapsPlacesGoogleLib implements IMapsPlaces
    {
        /**
         * @var String  $url_api URL to access API
         */
        private $url_api = "https://maps.googleapis.com/maps/api/";

        /**
         * @var String  $places_key_api Key of API authentication
         */
        private $places_key_api;

        /**
         * @var String  $sysLang Language of the system
         */
        private $sysLang;

        /**
         * @var String  $lang Language used on requests
         */
        private $lang;

        /**
         * @var String  $country Country used on requests
         */
        private $country;

        /**
         * Defined properties
         */
        public function __construct($placesKey = null)
        {
            if($placesKey)
                $this->places_key_api = $placesKey;
            else
                $this->places_key_api = GeolocationSettings::getPlacesKey();

            $this->sysLang = GeolocationSettings::getLocale();
        }

        /**
         * Return a list of addresses nearest of requester using Pelias Maps
         *
         * @param String       $text            String that represents part of andress.
         * @param Decimal      $requester_lat   Decimal that represents the requester latitude.
         * @param Decimal      $requester_lng   Decimal that represents the requester longitude.
         *
         * @return Array       [
         *                      'success',
         *                      'data' => [
         *                          [
         *                              'address'
         *                              'place_id'
         *                              'latitude',
         *                              'longitude',
         *                              'main_text',
         *                              'secondary_text', 
         *                          ],
         *                          ...
         *                      ],
         *                      'error_message'
         *                     ]
         */
        public function getAddressByTextWithLatLng($text, $requester_lat, $requester_lng)
        {
            $processed      =   [];
            $success        =   false;
            $error          =   [];

            if (!$this->url_api ||!$this->places_key_api || !$text || !$requester_lat || !$requester_lng)
            {
                $error      =   array("error_message" => trans('maps_lib.incomplete_parameters'));
            }
            else
            {
                $params         =   array(
                    "key"       =>  $this->places_key_api,
                    "location"  =>  $requester_lat . "," . $requester_lng,
                    "radius"    =>  5000,
                    "input"     =>  $text,
                    "language"  =>  "pt-BR"
                );

                $curl_string    =   $this->url_api . "place/autocomplete/json?" . http_build_query($params);
                $php_obj        =   self::curlCall($curl_string);
                $response_obj   =   json_decode($php_obj);

                if(
                    isset($response_obj->status) && 
                    $response_obj->status == "OK" && 
                    isset($response_obj->predictions) && 
                    count($response_obj->predictions) > 0)
                {
                    foreach($response_obj->predictions as $key => $prediction)
                    {
                        $proPrediction  = $this->processPlacesResponse($prediction);
                        if($proPrediction)
                            $processed[]= $proPrediction;
                    }
                    $success    =   true;
                }
                else if(isset($response_obj->predictions) && count($response_obj->predictions) == 0)
                {
                    $error      =   array("error_message" => trans('maps_lib.no_data_found'));
                }
            }

            $return = array("success" => $success, "data" => $processed);

            return count($error) ? array_merge($return, $error) : $return;
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
         * Processes the Places curl response and returns a formatted address array.
         *
         * @param Object      $prediction       A prediction object to be formatted.
         *
         * @return Array      $processed        Formatted array of addresses.
         */
        private function processPlacesResponse($prediction)
        {
            if(isset($prediction->place_id) && isset($prediction->description) && isset($prediction->structured_formatting)){
                $main_text = isset($prediction->structured_formatting->main_text) ? $prediction->structured_formatting->main_text : $prediction->description;
                $secondary_text = isset($prediction->structured_formatting->secondary_text) ? $prediction->structured_formatting->secondary_text : null;

                $processed['address']        =   $prediction->description;
                $processed['place_id']       =   $prediction->place_id;
                $processed['latitude']       =   null;
                $processed['longitude']      =   null;
                $processed['main_text']      =   $main_text;
                $processed['secondary_text'] =   $secondary_text;
            }else{
                $processed  =  false;
            }

            return $processed;
        }

        /**
         * Processes the Geocode curl response and returns a formatted address array.
         * 
         * @param Object      $prediction       A prediction object to be formatted.
         *
         * @return Array      $processed        Formatted array of addresses.
         */
        private function processGeocodeResponse($prediction)
        {
            if(
                property_exists($prediction[0], 'address_components') && 
                count($prediction[0]->address_components) > 0
            )
            {
                $processed['address']       =   $prediction[0]->formatted_address;
                $processed['place_id']      =   $prediction[0]->place_id;
                $processed['street_name']   =   $prediction[0]->address_components[1]->long_name;
                $processed['street_number'] =   $prediction[0]->address_components[0]->long_name;
                $processed['postal_code']   =   isset($prediction[0]->address_components[6]) ? $prediction[0]->address_components[6]->long_name : null;
                $processed['latitude']      =   $prediction[0]->geometry->location->lat;
                $processed['longitude']     =   $prediction[0]->geometry->location->lng;
            }
            else
            {
                $processed  =  false;
            }

            return $processed;
        }

        /**
         * A request with details about specific Google place_id.
         *
         * @param String      $placeId       Google place_id.
         *
         * @return Array      {
         *                      'success', 
         *                      'data' => [
         *                          'address'
         *                          'place_id'
         *                          'latitude',
         *                          'longitude',
         *                      ],
         *                      'error_message'
         *                     }
         */
        public function getDetailsById($placeId)
        {
            $error  =   [];

            if (!$this->url_api || !$this->places_key_api || !$placeId || empty($placeId))
            {
                $error  =   array("error_message" => trans('maps_lib.incomplete_parameters'));
            }
            else
            {
                $params         =   array(
                    "key"       =>  $this->places_key_api,
                    "place_id"  =>  $placeId,
                    "fields"    =>  "formatted_address,geometry,place_id",
                    "language"  =>  "pt-BR"
                );

                $curl_string    =   $this->url_api . "place/details/json?" . http_build_query($params);
                $php_obj        =   self::curlCall($curl_string);
                $response_obj   =   json_decode($php_obj);

                if(
                    isset($response_obj->status) && 
                    $response_obj->status == "OK" && 
                    isset($response_obj->result) && 
                    !empty($response_obj->result))
                {
                    $success                =   true;
                    $processed['address']   =   $response_obj->result->formatted_address;
                    $processed['place_id']  =   $response_obj->result->place_id;
                    $processed['latitude']  =   $response_obj->result->geometry->location->lat;
                    $processed['longitude'] =   $response_obj->result->geometry->location->lng;
                }else{
                    $success    =   false;
                    $processed  =   [];
                    $error      =   array("error_message" => trans('maps_lib.no_data_found'));
                }
            }

            $return = array("success" => $success, "data" => $processed);
            return count($error) ? array_merge($return, $error) : $return;

        }

        /**
         * Return a geocode attributes using Google Maps
         *
         * @param String       $address         String that represents andress.
         * @param String       $placeId         String hash that represents unique id of the place.
         * @param String       $lang            String that represents language used in request.
         *
         * @return Array       [
         *                      'success',
         *                      'data' => [
         *                          'place_id',
         *                          'street_name',
         *                          'street_number',
         *                          'postal_code',
         *                          'latitude',
         *                          'longitude',
         *                      ],
         *                      'error_message'
         *                     ]
         */
        public function getGeocodeWithAddress($address, $placeId = null, $lang = null)
        {
            $processed      =   [];
            $success        =   false;
            $error          =   [];

            if(!$this->url_api ||!$this->places_key_api || !$address)
            {
                $error      =   array("error_message" => trans('maps_lib.incomplete_parameters'));
            }
            else
            {
                $lang = isset($lang) ? $lang : $this->sysLang;
                $this->setCountryLang($lang);

                $default        =   array(
                    "key"       =>  $this->places_key_api,
                    "language"  =>  $this->lang
                );

                if($placeId && !empty($placeId))
                    $wanted = array("place_id" => $placeId);
                else
                    $wanted = array("address" => $address, "region" => $this->country);

                $params = array_merge($default, $wanted);

                $curl_string    =   $this->url_api . "geocode/json?" . http_build_query($params);
                $php_obj        =   self::curlCall($curl_string);
                $response_obj   =   json_decode($php_obj);
            }

            if(
                isset($response_obj->status) && 
                $response_obj->status == "OK" && 
                isset($response_obj->results) && 
                count($response_obj->results) > 0
            )
            {
                $processed  =   $this->processGeocodeResponse($response_obj->results);
                $success    =   true;
            }

            if(!count($processed) || !$processed)
            {
                $success    =   false;
                $error      =   array("error_message" => trans('maps_lib.no_data_found'));
            }

            $return = array("success" => $success, "data" => $processed);

            return count($error) ? array_merge($return, $error) : $return;
        }

        /**
         * Return attributes by reverse geocode Google Maps
         *
         * @param Decimal       $latitude         Decimal that represents the starting latitude of the request.
         * @param Decimal       $longitude        Decimal that represents the starting longitude of the request.
         *
         * @return Array       [
         *                      'success',
         *                      'data' => [
         *                          'address',
         *                          'place_id',
         *                          'street_name',
         *                          'street_number',
         *                          'postal_code',
         *                          'latitude',
         *                          'longitude',
         *                      ],
         *                      'error_message'
         *                     ]
         */
        public function getGeocodeByLatLng($latitude, $longitude)
        {
            $processed      =   [];
            $success        =   false;
            $error          =   [];

            if(!$this->url_api ||!$this->places_key_api || !$latitude || !$longitude)
            {
                $error      =   array("error_message" => trans('maps_lib.incomplete_parameters'));
            }
            else
            {
                $lang = isset($lang) ? $lang : $this->sysLang;
                $this->setCountryLang($lang);

                $params         =   array(
                    "key"       =>  $this->places_key_api,
                    "language"  =>  $this->lang,
                    "latlng"    =>  $latitude.",".$longitude
                );

                $curl_string    =   $this->url_api . "geocode/json?" . http_build_query($params);
                $php_obj        =   self::curlCall($curl_string);
                $response_obj   =   json_decode($php_obj);
            }

            if(
                isset($response_obj->status) && 
                $response_obj->status == "OK" && 
                isset($response_obj->results) && 
                count($response_obj->results) > 0
            )
            {
                $processed  =   $this->processGeocodeResponse($response_obj->results);
                $success    =   true;
            }

            if(!count($processed) || !$processed)
            {
                $success    =   false;
                $error      =   array("error_message" => trans('maps_lib.no_data_found'));
            }

            $return = array("success" => $success, "data" => $processed);

            return count($error) ? array_merge($return, $error) : $return;
        }

        /**
         * Setting the country and language received and forcing BCP 47 or ISO 639-1(language) and ccTLD(country).
         *
         * @param String        $langString         Language received from request.
         *
         * @return String                           Language string in BCP 47 or ISO 639-1 format.
         */
        private function setCountryLang($langString)
        {
            $langString = strtolower($langString);
            switch($langString)
            {
                case 'br':
                case 'pt-br':
                    $this->lang     = 'pt-BR';
                    $this->country  = 'bra';
                    return true;

                case 'ao':
                case 'pt':
                case 'pt-pt':
                case 'pt-ao':
                    $this->lang = 'pt-PT';
                    $this->country  = 'ago';
                    return true;

                case 'es':
                case 'es-ar':
                case 'es-co':
                case 'es-es':
                case 'es-mx':
                case 'es-us':
                case 'es-cl':
                    $this->lang = 'es';
                    $this->country  = 'esp';
                    return true;

                case 'en':
                case 'en-au':
                case 'en-ca':
                case 'en-gb':
                case 'en-ie':
                case 'en-in':
                case 'en-nz':
                case 'en-us':
                case 'en-za':
                    $this->lang = 'en';
                    $this->country  = 'usa';
                    return true;

                default:
                    $this->lang = 'en';
                    $this->country  = 'usa';
                    return true;
            }
        }
    }

?>
