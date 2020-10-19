<?php

namespace Codificar\Geolocation\Lib;

//Internal Uses
use Codificar\Geolocation\Models\GeolocationSettings;
use Codificar\Geolocation\Lib\Places\IMapsPlaces;

    /**
     * Geolocation requests on Algolia Maps API
     */
    class MapsPlacesAlgoliaLib implements IMapsPlaces
    {
        /**
         * @var String  $url_api URL to access API
         */
        private $url_api = "https://places-dsn.algolia.net/1/places/";

        /**
         * @var String  $places_key_api Key of API authentication
         */
        private $places_key_api;

        /**
         * @var String  $places_application_id ID of application
         */        
        private $places_application_id;

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
        public function __construct($placesKey = null, $aplicationId = null)
        {
            if($placesKey)
                $this->places_key_api = $placesKey;
            else
                $this->places_key_api = GeolocationSettings::getPlacesKey();

            if($aplicationId)
                $this->places_application_id = $aplicationId;
            else
                $this->places_application_id = GeolocationSettings::getPlacesApplicationId();

            $this->sysLang = GeolocationSettings::getLocale();
        }

        /**
         * Return a list of addresses nearest of requester using Algolia Maps
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
         *                           ],
         *                         ...
         *                      ],
         *                      'error_message'
         *                     ]
         */
        public function getAddressByTextWithLatLng($text, $requester_lat, $requester_lng)
        {
            $processed      =   [];
            $success        =   false;
            $error          =   [];

            if (
                !$this->url_api || 
                !$this->places_key_api || 
                !$this->places_application_id || 
                !$text || 
                !$requester_lat || 
                !$requester_lng
            )
            {
                $error  =  array("error_message" => trans('maps_lib.incomplete_parameters'));
            }
            else
            {
                $this->setCountryLang($this->sysLang);

                $header = array(
                    "X-Algolia-Application-Id" => $this->places_application_id,
                    "X-Algolia-API-Key"        => $this->places_key_api
                );

                $params         =   array(
                    "aroundLatLng"      => implode(',', [$requester_lat, $requester_lng]),
                    "query"             => $text,
                    "language"          => $this->lang,
                    "countries"         => $this->country,
                    "aroundLatLngViaIP" => false
                );

                $curl_string    =   $this->url_api . "query?" . http_build_query($params);
                $php_obj        =   self::curlCall($header, $curl_string);
                $response_obj   =   json_decode($php_obj);

                if(isset($response_obj->hits) && count($response_obj->hits) > 0)
                {
                    $processed  =   self::processPlacesResponse($response_obj->hits);
                    $success    =   true;
                }
                else if(isset($response_obj->hits) && count($response_obj->hits) == 0)
                {
                    $error      =   array("error_message" => trans('maps_lib.no_data_found'));
                }

            }

            $return = array("success" => $success, "data" => $processed);

            return count($error) ? array_merge($return, $error) : $return;
        }

        /**
         * Return a geocode attributes using Algolia Maps
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

            if (
                !$this->url_api || 
                !$this->places_key_api || 
                !$this->places_application_id || 
                !$address
            )
            {
                $error  =  array("error_message" => trans('maps_lib.incomplete_parameters'));
            }
            else
            {
                $lang   =   isset($lang) ? $lang : $this->sysLang;
                $this->setCountryLang($lang);

                $header = array(
                    "X-Algolia-Application-Id" => $this->places_application_id,
                    "X-Algolia-API-Key"        => $this->places_key_api
                );

                $params         =   array(
                    "query"             => $address,
                    "language"          => $this->lang,
                    "countries"         => $this->country,
                    "aroundLatLngViaIP" => false
                );

                $curl_string    =   $this->url_api . "query?" . http_build_query($params);
                $php_obj        =   self::curlCall($header, $curl_string);
                $response_obj   =   json_decode($php_obj);

                if(isset($response_obj->hits) && count($response_obj->hits) > 0)
                {
                    $processed  =   self::processGeocodeResponse($response_obj->hits);
                    $success    =   true;
                }
                else
                {
                    $error      =   array("error_message" => trans('maps_lib.no_data_found'));
                }

            }

            $return = array("success" => $success, "data" => $processed);

            return count($error) ? array_merge($return, $error) : $return;
        }

        /**
         * Return attributes by reverse geocode Algolia Maps
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

            if(
                !$this->url_api || 
                !$this->places_key_api || 
                !$this->places_application_id || 
                !$latitude || 
                !$longitude
            )
            {
                $error  =   array("error_message" => trans('maps_lib.incomplete_parameters'));
            }
            else
            {
                $lang   =   isset($lang) ? $lang : $this->sysLang;
                $this->setCountryLang($lang);

                $header =   array(
                    "X-Algolia-Application-Id" => $this->places_application_id,
                    "X-Algolia-API-Key"        => $this->places_key_api
                );

                $params         =   array(
                    "aroundLatLng"      =>  $latitude .",". $longitude,
                    "language"          =>  $this->lang,
                    "hitsPerPage"       =>  1
                );

                $curl_string    =   $this->url_api .'reverse?'. http_build_query($params);
                $php_obj        =   self::curlCall($header, $curl_string);
                $response_obj   =   json_decode($php_obj);
            }

            if(isset($response_obj->hits) && count($response_obj->hits) > 0)
            {
                $processed  =   self::processGeocodeResponse($response_obj->hits);
                $success    =   true;
            }
            else
            {
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
        private static function curlCall($header, $curl_string)
        {
            $session = curl_init($curl_string);
            curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($session, CURLOPT_HTTPHEADER, $header);            
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
            foreach($response_obj as $key => $feature)
            {
                $street   = isset($feature->locale_names[0]) ? $feature->locale_names[0] . ", " : null;
                $district = isset($feature->suburb[0]) ? $feature->suburb[0] . ", " : null;
                $city     = isset($feature->city[0]) ? $feature->city[0] . ", " : null;
                $state    = isset($feature->administrative[0]) ? $feature->administrative[0] . ", " : null;
                $country  = isset($feature->country) ? $feature->country : null;
                $mainDistrict = isset($feature->suburb[0]) ? $feature->suburb[0] : null;

                $processed[$key]['address']    =  $street . $district . $city . $state . $country;
                $processed[$key]['place_id']   =  null;
                $processed[$key]['latitude']   =  isset($feature->_geoloc->lat) ? $feature->_geoloc->lat : null;
                $processed[$key]['longitude']  =  isset($feature->_geoloc->lng) ? $feature->_geoloc->lng : null;
                $processed[$key]['main_text']  =  $street . $mainDistrict;
                $processed[$key]['secondary_text'] = $city . $state . $country;
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
        private static function processGeocodeResponse($prediction)
        {
            if(
                is_array($prediction) && 
                count($prediction) > 0 && 
                isset($prediction[0]->locale_names)
            )
            {
                $street = isset($prediction[0]->locale_names[0]) ? $prediction[0]->locale_names[0] : null;
                $postalCode = isset($prediction[0]->postcode[0]) ? $prediction[0]->postcode[0] : null;
                $suburb = isset($prediction[0]->suburb[0]) ? ' - ' . $prediction[0]->suburb[0] : '';
                $city = isset($prediction[0]->city[0]) ? ', ' . $prediction[0]->city[0] : '';
                $state = isset($prediction[0]->administrative[0]) ? ', ' . $prediction[0]->administrative[0] : '';
                $country = isset($prediction[0]->country) ? ', ' . $prediction[0]->country : '';
                $address = $street . $suburb . $city . $state . $country;

                $processed['place_id']      =   null;
                $processed['street_name']   =   $street;
                $processed['street_number'] =   null;
                $processed['postal_code']   =   $postalCode;
                $processed['latitude']      =   isset($prediction[0]->_geoloc->lat) ? $prediction[0]->_geoloc->lat : null;
                $processed['longitude']     =   isset($prediction[0]->_geoloc->lng) ? $prediction[0]->_geoloc->lng : null;
                $processed['address']       =   $address;
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
                    $this->country  = 'br';
                    return true;

                case 'ao':
                case 'pt':
                case 'pt-pt':
                case 'pt-ao':
                    $this->lang = 'pt-PT';
                    $this->country  = 'ao';
                    return true;

                case 'es':
                case 'es-ar':
                case 'es-co':
                case 'es-es':
                case 'es-mx':
                case 'es-us':
                case 'es-cl':
                    $this->lang = 'es';
                    $this->country  = 'es';
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
                    $this->country  = 'us';
                    return true;

                default:
                    $this->lang = 'en';
                    $this->country  = 'us';
                    return true;
            }
        }

    }