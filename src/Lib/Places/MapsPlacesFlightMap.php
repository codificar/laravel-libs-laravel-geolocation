<?php

namespace Codificar\Geolocation\Lib\Places;

//Internal uses
use Codificar\Geolocation\Models\GeolocationSettings;
use Codificar\Geolocation\Lib\Places\IMapsPlaces;

    /**
     * Geolocation requests on Google Maps API
     */
    class MapsPlacesFlightMap implements IMapsPlaces
    {
        /**
         * @var String  $url_api URL to access API
         */
        private $url_api = "https://maps.flightmap.io/api/";

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
                $lang = isset($lang) ? $lang : $this->sysLang;
                $this->setCountryLang($lang);

                $params         =   array(
                    "fm_token"       =>  $this->places_key_api,
                    "currentlatitude"  =>  $requester_lat,
                    "currentlongitude"  =>  $requester_lng,
                    "radius"    =>  5000,
                    "text"     =>  $text,
                    "language"  =>  $lang
                );

                $curl_string    =   $this->url_api . "search?" . http_build_query($params);
                $php_obj        =   self::curlCall($curl_string);
                $response_obj   =   json_decode($php_obj);
               
                if(
                    isset($response_obj->status) && 
                    $response_obj->status == 200 && 
                    isset($response_obj->data) && 
                    count($response_obj->data) > 0)
                {
                    foreach($response_obj->data as $key => $prediction)
                    {                        
                        $proPrediction  = $this->processPlacesResponse($prediction);
                        if($proPrediction)
                            $processed[]= $proPrediction;
                    }
                    $success    =   true;
                }
                else if(isset($response_obj->status) == 400 && $response_obj->message != "Successful")
                {
                    $error      =   array("error_message" => $response_obj->message);
                }
                else if(isset($response_obj->data) && count($response_obj->data) == 0)
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
            if(isset($prediction->address)){
                $addressArray = explode(",",$prediction->address);
              
                $contetSize = sizeof($addressArray);
                $estate = $addressArray[$contetSize-1];

                $address = $addressArray[0]." -".$addressArray[1]."-".$estate.",".end($addressArray);
             
                $main_text = $addressArray[0]." -".$addressArray[1];
                $secondary_text = " -".$estate.",".end($addressArray);

                $processed['address']        =   $address;
                $processed['place_id']       =   null;
                $processed['latitude']       =   $prediction->lat;
                $processed['longitude']      =   $prediction->lng;
                $processed['main_text']      =   $main_text;
                $processed['secondary_text'] =   $secondary_text;
            }else{
                $processed  =  false;
            }

            return $processed;
        }

        /**
         * Processes the Reverse Geocode curl response and returns a formatted address array.
         * 
         * @param Object      $prediction       A prediction object to be formatted.
         * @param Decimal      $requester_lat   Decimal that represents the requester latitude.
         * @param Decimal      $requester_lng   Decimal that represents the requester longitude.
         *
         * @return Array      $processed        Formatted array of addresses.
         */
        private function processReverseGeocodeResponse($prediction, $latitude, $longitude)
        {
           
            $addressArray = $prediction->address_components[0];
            // Get address Number and CEP
          
            $formattedAddress = $addressArray->route.", ".$addressArray->street_number.$addressArray->sublocality_level_1." - ".
            $addressArray->administrative_area_level_1.", ".$addressArray->postal_code.", ".$addressArray->country;       

            if(
                isset($addressArray)
            )
            {
                $processed['address']       =   $formattedAddress;
                $processed['place_id']      =   null;
                $processed['street_name']   =   $addressArray->route;
                $processed['street_number'] =   $addressArray->street_number;
                $processed['postal_code']   =   $addressArray->postal_code;
                $processed['latitude']      =   $latitude;
                $processed['longitude']     =   $longitude;
            }
            else
            {
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
            $address = $prediction->address;
            // Get address Number and CEP
            $re = '/[0-9]+/im';                 
            preg_match_all($re, $address, $matches, PREG_SET_ORDER, 0);   
            $streetNumber = $matches[0][0];
            $postalCode = isset($matches[1]) ? $matches[1][0] : null;
            if(strlen($streetNumber) >= 4){
               $postalCode = $streetNumber;              
            }

            $addressArray = explode(",", $address);
            $contetSize = sizeof($addressArray);
            $estate = $addressArray[$contetSize-1];
            $formattedAddress = $addressArray[0]." -".$addressArray[1]." -".$estate.",".end($addressArray);           
            $streetName = $addressArray[0];

            if(
                isset($addressArray)
            )
            {
                $processed['address']       =   $formattedAddress;
                $processed['place_id']      =   null;
                $processed['street_name']   =   $streetName;
                $processed['street_number'] =   $streetNumber;
                $processed['postal_code']   =   $postalCode;
                $processed['latitude']      =   $prediction->lat;
                $processed['longitude']     =   $prediction->lng;
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
        public function getGeocodeWithAddress($address, $placeId = null, $lang = null, $latitude = null, $longitude = null)
        {           
            $processed      =   [];
            $success        =   false;
            $error          =   [];
           
            if(!$this->url_api ||!$this->places_key_api || !$address || $latitude == null || $longitude == null)
            {               
                $error      =   array("error_message" => trans('maps_lib.incomplete_parameters'));
            }
            else
            {               
                $lang = isset($lang) ? $lang : $this->sysLang;
                $this->setCountryLang($lang);

                $params         =   array(
                    "fm_token"       =>  $this->places_key_api,                    
                    "radius"    =>  5000,
                    "text"     =>  $address,
                    "language"  =>  $lang,
                    "currentlatitude"  =>  $latitude,
                    "currentlongitude"  =>  $longitude,
                );

                $curl_string    =   $this->url_api . "search?" . http_build_query($params);
                $php_obj        =   self::curlCall($curl_string);
                $json_data      = json_decode($php_obj);
                $response_obj   =   $json_data;
            }
          
            if(
                isset($response_obj->status) && 
                $response_obj->status == 200 && 
                isset($response_obj->data) && 
                count($response_obj->data) > 0
            )
            {
               
                $locate   =   $json_data->data[0];
                $processed  =   $this->processGeocodeResponse($locate);
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
                    "fm_token"       =>  $this->places_key_api,
                    "lat"  =>  $latitude,
                    "lng"    =>  $longitude,
                    "zoom" => 18,
                    "language"  =>  $lang
                );

                $curl_string    =   $this->url_api . "search_reverse?" . http_build_query($params);
                $php_obj        =   self::curlCall($curl_string);
                $response_obj   =   json_decode($php_obj);                 
            }
            
            if(
                isset($response_obj->message) &&                
                isset($response_obj->status) && 
                isset($response_obj->data) &&
                $response_obj->message == "Successful" &&
                $response_obj->status == 200
            ){
                $processed  =   $this->processReverseGeocodeResponse($response_obj->data, $latitude, $longitude);
                $success    =   true;
            }else {
                $success    =   false;
                $error      =   array("error_message" => trans('maps_lib.no_data_found'));
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
