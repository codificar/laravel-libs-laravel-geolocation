<?php

namespace Codificar\Geolocation\Lib\Places;

//Internal uses
use Codificar\Geolocation\Models\GeolocationSettings;
use Codificar\Geolocation\Lib\Places\IMapsPlaces;

    /**
     * Geolocation requests on Google Maps API
     */
    class MapsPlacesOpenRoute implements IMapsPlaces
    {
        /**
         * @var String  $url_api URL to access API
         */
        private $url_api = "https://api.openrouteservice.org/geocode/";

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
                    "api_key"       =>  $this->places_key_api,
                    "focus.point.lat"  =>  $requester_lat,
                    "focus.point.lon"  =>  $requester_lng,                   
                    "text"     =>  $text,
                    "layers"  =>  "address,locality,neighbourhood,country,region"               
                );

                $curl_string    =   $this->url_api . "autocomplete?" . http_build_query($params);
                $php_obj        =   self::curlCall($curl_string);
                $response_obj   =   json_decode($php_obj);
              
                if(
                    isset($response_obj->features) && 
                    count($response_obj->features) > 0)
                {
                    $limitCount = 0;
                    foreach($response_obj->features as $key => $prediction)
                    {                        
                        $proPrediction  = $this->processPlacesResponse($prediction);
                        if($proPrediction) $processed[]= $proPrediction;
                        $limitCount++;
                        if($limitCount >= 5) break;
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
            if(isset($prediction->properties) && isset($prediction->geometry)){

                // {
                //     "address": "Rua Alto do Tanque - Nossa Senhora das Gracas, Santa Luzia - MG, Brasil",
                //     "place_id": "EkdSdWEgQWx0byBkbyBUYW5xdWUgLSBOb3NzYSBTZW5ob3JhIGRhcyBHcmFjYXMsIFNhbnRhIEx1emlhIC0gTUcsIEJyYXNpbCIuKiwKFAoSCQ3S-GoChKYAEZeZdGjL3XUPEhQKEgnvz7p0r4amABHTcuve102w-g",
                //     "latitude": null,
                //     "longitude": null,
                //     "main_text": "Rua Alto do Tanque",
                //     "secondary_text": "Nossa Senhora das Gracas, Santa Luzia - MG, Brasil"
                //   },

              
                $city = $prediction->properties->locality;
                $estate = $prediction->properties->region_a;
                $country = $prediction->properties->country;
                $secondary_text = $prediction->properties->name." -".$estate.",".$country;
                $processed['address']        =   $prediction->properties->label;
                $processed['place_id']       =   null;
                $processed['latitude']       =   $prediction->geometry->coordinates[1];
                $processed['longitude']      =   $prediction->geometry->coordinates[0];
                $processed['main_text']      =   $prediction->properties->name;
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
                isset($prediction)
            )
            {              
                $processed['address']       =    $prediction->properties->label;
                $processed['place_id']      =    null;
                $processed['street_name']   =    $prediction->properties->name;
                $processed['street_number'] =    $prediction->properties->housenumber;
                $processed['postal_code']   =    $prediction->properties->postalcode;
                $processed['latitude']      =    $prediction->geometry->coordinates[1];
                $processed['longitude']     =    $prediction->geometry->coordinates[0];
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
                    "language"  =>  $this->sysLang
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
                    "api_key"       =>  $this->places_key_api,
                    "focus.point.lat"  =>  $latitude,
                    "focus.point.lon"  =>  $longitude,                   
                    "text"     =>  $address,
                    "layers"  =>  "address,locality,neighbourhood"               
                );

                $curl_string    =   $this->url_api . "autocomplete?" . http_build_query($params);
                $php_obj        =   self::curlCall($curl_string);
                $response_obj   =   json_decode($php_obj);
            }
          
            if(
                isset($response_obj->features) && 
                count($response_obj->features) > 0) {
            
             
                $locate   =  $response_obj->features[0];
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
                    "api_key"    => $this->places_key_api,
                    "point.lon"  =>  $longitude,
                    "point.lat"  =>  $latitude,
                    "layers"  =>  "address,locality,neighbourhood"               
                );

                $curl_string    =   $this->url_api . "reverse?" . http_build_query($params);
                $php_obj        =   self::curlCall($curl_string);
                $response_obj   =   json_decode($php_obj);                 
            }
          
            if(
                isset($response_obj->features) && 
                count($response_obj->features) > 0) {
                  
                $processed  =   $this->processGeocodeResponse(
                    $response_obj->features[0]
                );
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
