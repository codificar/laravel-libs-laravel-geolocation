<?php

namespace Codificar\Geolocation\Lib;

//Internal uses
use Codificar\Geolocation\Models\GeolocationSettings;
use Codificar\Geolocation\Lib\Places\IMapsPlaces;

    /**
     * Geolocation requests on Pelias Maps API
     */
    class MapsPlacesPeliasLib implements IMapsPlaces
    {
        /**
         * @var String  $url_api URL to access API
         */
        private $url_api = "https://api.geocode.earth/v1";

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
        public function __construct($urlApi = null, $placesKey = null)
        {
            if($urlApi){
                $this->url_api = $urlApi;
            }                
            else if(GeolocationSettings::getPlacesUrl()){
                $this->url_api = GeolocationSettings::getPlacesUrl();
            }
            
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
         * @return Array       ['success', 'data' => [
         *                                              [
         *                                                  'address'
         *                                                  'place_id'
         *                                                  'latitude',
         *                                                  'longitude',
         *                                                  'main_text',
         *                                                  'secondary_text', 
         *                                              ],
         *                                              ...
         *                                            ],
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
                    "layers"                =>  "address,venue,neighbourhood,locality,borough,localadmin,county,macrocounty,region,macroregion,country,coarse,postalcode",
                    "api_key"               =>  $this->places_key_api,
                    "focus.point.lat"       =>  $requester_lat,
                    "focus.point.lon"       =>  $requester_lng,
                    "boundary.circle.lat"   =>  $requester_lat,
                    "boundary.circle.lon"   =>  $requester_lng,
                    "boundary.circle.radius"=>  800,
                    "text"                  =>  $text
                );

                $curl_string    =   $this->url_api . "/autocomplete?" . http_build_query($params);
                $php_obj        =   self::curlCall($curl_string);
                $response_obj   =   json_decode($php_obj);

                if(isset($response_obj->features) && count($response_obj->features) > 0){
                    $processed  =   self::processPlacesResponse($response_obj->features);
                    $success    =   true;
                }else if(isset($response_obj->features) && count($response_obj->features) == 0){
                    $error      =   array("error_message" => trans('maps_lib.no_data_found'));
                }

            }

            $return = array("success" => $success, "data" => $processed);

            return count($error) ? array_merge($return, $error) : $return;
        }

        /**
         * Return a geocode attributes using Pelias Maps
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

            if(!$this->url_api ||!$this->places_key_api || !$address)
            {
                $error      =   array("error_message" => trans('maps_lib.incomplete_parameters'));
            }
            else
            {
                $lang = isset($lang) ? $lang : $this->sysLang;
                $this->setCountryLang($lang);

                $params         =   array(
                    "api_key"           =>  $this->places_key_api,
                    "text"              =>  $address,
                    "boundary.country"  =>  $this->country,
                    "size"              =>  1
                );

                $curl_string    =   $this->url_api . "/search?" . http_build_query($params);
                $php_obj        =   self::curlCall($curl_string);
                $response_obj   =   json_decode($php_obj);
            }

            if(
                is_object($response_obj) && 
                isset($response_obj->geocoding) && 
                !property_exists($response_obj->geocoding, 'errors')
            )
            {
                $processed  =   $this->processGeocodeResponse($response_obj);
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
         * Return attributes by reverse geocode Pelias Maps
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
                    "api_key"           =>  $this->places_key_api,
                    "point.lat"         =>  $latitude,
                    "point.lon"         =>  $longitude,
                    "boundary.country"  =>  $this->country,
                    "size"              =>  1
                );

                $curl_string    =   $this->url_api .'/reverse?'. http_build_query($params);
                $php_obj        =   self::curlCall($curl_string);
                $response_obj   =   json_decode($php_obj);
            }

            if(
                is_object($response_obj) && 
                isset($response_obj->geocoding) && 
                !property_exists($response_obj->geocoding, 'errors')
            )
            {
                $processed  =   $this->processGeocodeResponse($response_obj);
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
         * @param Object      $response_obj        Response object by curl.
         *
         * @return Array      $processed           Formatted array of addresses.
         */
        private static function processPlacesResponse($response_obj)
        {
            foreach($response_obj as $key => $feature){
                
                $main_text       =   isset($feature->properties->name) ? $feature->properties->name : null;
                $district        =   isset($feature->properties->neighbourhood) ?  $feature->properties->neighbourhood : null;
                $number          =   isset($feature->properties->housenumber) ?  $feature->properties->housenumber : null;
                $city            =   isset($feature->properties->county) ? $feature->properties->county : null;
                $state           =   isset($feature->properties->region) ? $feature->properties->region : null;
                $country         =   isset($feature->properties->country) ? $feature->properties->country : null;
                
                $processed[$key]['address']         =  isset($feature->properties->street) ? $feature->properties->street : $main_text;
                
                if(isset($feature->properties->housenumber)){
                    $processed[$key]['address'] .= " ". $number;
                }
                
                $processed[$key]['address']         .= $district && $district != $main_text ? ", ".$district : '';
                $processed[$key]['address']         .= $city && $city != $main_text ? ", " . $city : '';
                $processed[$key]['address']         .= $state && $state != $main_text ? ", " . $state : '';
                $processed[$key]['address']         .= $processed[$key]['address'] && $country  && $country != $main_text ? ", ". $country : '';
                $processed[$key]['place_id']        =  null;
                $processed[$key]['latitude']        =  $feature->geometry->coordinates[1];
                $processed[$key]['longitude']       =  $feature->geometry->coordinates[0];
                $processed[$key]['main_text']       =  isset($feature->properties->name) ? $feature->properties->name : $processed[$key]['address'];                
                $processed[$key]['secondary_text']  =  $city . ", ". $state . ", " . $country;
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
                isset($prediction->features) && 
                count($prediction->features) > 0 && 
                isset($prediction->features[0]->geometry->coordinates)
            )
            {
                $street = isset($prediction->features[0]->properties->street) ? $prediction->features[0]->properties->street : (isset($prediction->geocoding->query->parsed_text->street) ? $prediction->geocoding->query->parsed_text->street : null);
                $houseNumber = isset($prediction->features[0]->properties->housenumber) ? $prediction->features[0]->properties->housenumber : (isset($prediction->geocoding->query->parsed_text->housenumber) ? $prediction->geocoding->query->parsed_text->housenumber : null);
                $postalCode = isset($prediction->features[0]->properties->postalcode) ? $prediction->features[0]->properties->postalcode : (isset($prediction->geocoding->query->parsed_text->postalcode) ? $prediction->geocoding->query->parsed_text->postalcode : null);
                $address = isset($prediction->features[0]->properties->label) ? $prediction->features[0]->properties->label : null ;

                $processed['place_id']      =   null;
                $processed['street_name']   =   $street;
                $processed['street_number'] =   $houseNumber;
                $processed['postal_code']   =   $postalCode;
                $processed['latitude']      =   $prediction->features[0]->geometry->coordinates[1];
                $processed['longitude']     =   $prediction->features[0]->geometry->coordinates[0];
                $processed['address']       =   ($address ? $address : sprintf("%s %s %s", $street, $houseNumber, $postalCode));
            }
            else
            {
                $processed  =  false;
            }

            return $processed;
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