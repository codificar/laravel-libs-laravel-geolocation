<?php

namespace Codificar\Geolocation\Lib\Places;

//Internal uses
use Codificar\Geolocation\Models\GeolocationSettings;
use Codificar\Geolocation\Lib\Places\IMapsPlaces;

    /**
     * Geolocation requests on Bing Maps API
     */
    class MapsPlacesBing implements IMapsPlaces
    {
        /**
         * @var String  $url_api URL to access API
         */
        private $url_api = "https://dev.virtualearth.net/REST/v1";

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
                $error      =   array("error_message" => trans('geolocationTrans::geolocation.incomplete_parameters'));
            }
            else
            {
                $lang = isset($lang) ? $lang : $this->sysLang;
                $this->setCountryLang($lang);

                $params         =   array(      
                    "culture" =>  $lang,           
                    "query"     =>  $text,
                    "key"       =>  $this->places_key_api
                );
                
                $curl_string    =   $this->url_api . "/Locations/?" . http_build_query($params);
                $php_obj        =   self::curlCall($curl_string);
                $response_obj   =   json_decode($php_obj);
                
                if(
                    isset($response_obj->statusCode) && 
                    $response_obj->statusCode == 200 && 
                    isset($response_obj->resourceSets) && 
                    count($response_obj->resourceSets) > 0)
                {
                    $data = $response_obj->resourceSets[0]->resources;
                    foreach($data as $key => $prediction)
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
                    \Log::info(__FUNCTION__.":: response_obj = ".print_r($response_obj,1));
                    $error      =   array("error_message" => trans('geolocationTrans::geolocation.no_data_found'));
                }else {
                    \Log::info(__FUNCTION__.":: curl_string = ".print_r($curl_string,1));

                    \Log::info(__FUNCTION__.":: response_obj = ".print_r($response_obj,1));
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
                $addressArray = $prediction->address;
                $points = ($prediction)->point->coordinates;
              
                $main_text = $addressArray->formattedAddress;

                isset($addressArray->locality) ? $locality = $addressArray->locality : $locality = $addressArray->countryRegion;

                isset($addressArray->adminDistrict) ? $district = $addressArray->adminDistrict : $district = "";

                $secondary_text = $locality." - ".$district.", ".$addressArray->countryRegion;
                
                $processed['address']        =   $main_text." - ".$district.", ".$addressArray->countryRegion;
                $processed['place_id']       =   null;
                $processed['latitude']       =   $points[0];
                $processed['longitude']      =   $points[1];
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
            $address = $prediction->address;          

            if(
                isset($address)
            )
            {
                  // Get address Number and CEP
                $re = '/[0-9]+/im';                 
                preg_match_all($re, $address->addressLine, $matches, PREG_SET_ORDER, 0);   
                
                $streetNumber = isset($matches[0][0]) ? $matches[0][0] : null;
                $postalCode = isset($address->postalCode) ? $address->postalCode : null;
            
                $points = $prediction->point->coordinates;

                $processed['address']       =   $address->formattedAddress;
                $processed['place_id']      =   null;
                $processed['street_name']   =   $address->addressLine;
                $processed['street_number'] =   $streetNumber;
                $processed['postal_code']   =   $postalCode;
                $processed['latitude']      =   $points[0];
                $processed['longitude']     =   $points[1];
            }
            else
            {
                $processed  =  false;
            }
            return $processed;
        }

        /**
         * A request with details about specific Bing place_id.
         *
         * @param String      $placeId       Bing place_id.
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
        
            $success    =   false;
            $processed  =   [];
            $error      =   array("error_message" => trans('geolocationTrans::geolocation.no_data_found'));               

            $return = array("success" => $success, "data" => $processed);
            return count($error) ? array_merge($return, $error) : $return;
        }

        /**
         * Return a geocode attributes using Bing Maps
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
                $error      =   array("error_message" => trans('geolocationTrans::geolocation.incomplete_parameters'));
            }
            else
            {     
                 
                $lang = isset($lang) ? $lang : $this->sysLang;
                $this->setCountryLang($lang);

                $params         =   array(     
                    "culture" =>  $lang,                
                    "query"     =>  $address,
                    "key"       =>  $this->places_key_api
                );
                
                $curl_string    =   $this->url_api . "/Locations/?" . http_build_query($params);
                $php_obj        =   self::curlCall($curl_string);
                $response_obj   =   json_decode($php_obj);
                      
            }
          
            if(
                isset($response_obj->statusCode) && 
                $response_obj->statusCode == 200 && 
                isset($response_obj->resourceSets) && 
                count($response_obj->resourceSets) > 0)            
            {
                $locate   =   $response_obj->resourceSets[0]->resources[0];
                $processed  =   $this->processGeocodeResponse($locate);
                $success    =   true;
            }
            
            if(!count($processed) || !$processed)
            {
                $success    =   false;
                $error      =   array("error_message" => trans('geolocationTrans::geolocation.no_data_found'));
            }

            $return = array("success" => $success, "data" => $processed);

            return count($error) ? array_merge($return, $error) : $return;
        }

        /**
         * Return attributes by reverse geocode Bing Maps
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
           
            if(!$this->url_api ||!$this->places_key_api || $latitude == null || $longitude == null)
            {               
                $error      =   array("error_message" => trans('geolocationTrans::geolocation.incomplete_parameters'));
            }
            else
            {     
                 
                $lang = isset($lang) ? $lang : $this->sysLang;
                $this->setCountryLang($lang);

                $params         =   array(
                    "culture" =>  $lang,
                    "key"       =>  $this->places_key_api
                );
               
                $curl_string    =   $this->url_api . "/Locations/".$latitude.",".$longitude."?".http_build_query($params);
               
                $php_obj        =   self::curlCall($curl_string);
                $json_data      = json_decode($php_obj);
                $response_obj   =   $json_data;
            }
          
            if(
                isset($response_obj->statusCode) && 
                $response_obj->statusCode == 200 && 
                isset($response_obj->resourceSets) && 
                count($response_obj->resourceSets) > 0)            
            {
                $locate   =   $response_obj->resourceSets[0]->resources[0];
                $processed  =   $this->processGeocodeResponse($locate);
                $success    =   true;
            }
            
            if(!count($processed) || !$processed)
            {
                $success    =   false;
                $error      =   array("error_message" => trans('geolocationTrans::geolocation.no_data_found'));
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
