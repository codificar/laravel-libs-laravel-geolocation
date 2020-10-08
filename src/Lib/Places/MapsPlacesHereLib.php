<?php

namespace Codificar\Geolocation\Lib;

//Internal Uses
use Codificar\Geolocation\Models\GeolocationSettings;

class MapsPlacesHereLib implements IMapsPlaces {

    /**
     * @var String  $url_api         URL to access Places API
     * @var String  $geocode_url_api URL to access Geocode API
     * @var String  $reverse_url_api URL to access Reverse Geocode API
     */
    private $url_api = "https://places.ls.hereapi.com/places/v1/autosuggest?";
    private $geocode_url_api = "https://geocoder.ls.hereapi.com/6.2/geocode.json?";
    private $reverse_url_api = "https://reverse.geocoder.ls.hereapi.com/6.2/reversegeocode.json?";

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
     * Return a list of addresses nearest of requester using Here Maps
     *
     * @param String       $text            String that represents part of andress.
     * @param Decimal      $requester_lat   Decimal that represents the requester latitude.
     * @param Decimal      $requester_lng   Decimal that represents the requester longitude.
     *
     * @return Array       [
     *                      'success',
     *                      'data' => [
     *                        [
     *                            'address'
     *                            'place_id'
     *                            'latitude',
     *                            'longitude',
     *                            'main_text',
     *                            'secondary_text', 
     *                        ],
     *                        ...
     *                      ],
     *                      'error_message'
     *                     ]
     */
    public function getAddressByTextWithLatLng($text, $requester_lat, $requester_lng)
    {
        $processed      =   [];
        $success        =   false;
        $error          =   [];

        if (!$this->url_api ||!$this->places_key_api || !$text || !$requester_lat || !$requester_lng) {
            $error      =   array("error_message" => trans('maps_lib.incomplete_parameters'));
        } else {
            $params         =   array(
                "apiKey" =>  $this->places_key_api,
                "at" =>  $requester_lat . "," . $requester_lng,
                "q" =>  $text,
                "size" => 5,
                "result_types" => 'address, place'
            );
    
            $curl_string    =   $this->url_api . http_build_query($params);
            $php_obj        =   self::curlCall($curl_string);
            $response_obj   =   json_decode($php_obj);  
        }

        if ( is_object($response_obj) && property_exists($response_obj, 'results') && count($response_obj->results) > 0) {

            foreach($response_obj->results as $item){
                $proPrediction  = $this->processPlacesResponse($item);
                if($proPrediction)
                    $processed[]= $proPrediction;
            }

            $success    =   true;
        } else if(isset($response_obj->predictions) && count($response_obj->predictions) == 0){
            $error      =   array("error_message" => trans('maps_lib.no_data_found'));
        }

        $return = array("success" => $success, "data" => $processed);

        return count($error) ? array_merge($return, $error) : $return;
    }

    /**
     * Return a geocode attributes using Here Maps
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

        if(!$this->geocode_url_api ||!$this->places_key_api || !$address)
        {
            $error      =   array("error_message" => trans('maps_lib.incomplete_parameters'));
        }
        else
        {
            $lang = isset($lang) ? $lang : $this->sysLang;
            $this->setCountryLang($lang);

            $params         =   array(
                "apiKey"    =>  $this->places_key_api,
                "searchtext"=>  $address,
                "language"  =>  $this->lang,
                "country"   =>  $this->country
            );

            $curl_string    =   $this->geocode_url_api . http_build_query($params);
            $php_obj        =   self::curlCall($curl_string);
            $response_obj   =   json_decode($php_obj);  
        }

        if(is_object($response_obj) && !property_exists($response_obj, 'error') && count($response_obj->Response->View) > 0)
        {
            $processed  =   $this->processGeocodeResponse($response_obj->Response->View[0]);
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
     * Return attributes by reverse geocode Here Maps
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

        if(!$this->reverse_url_api ||!$this->places_key_api || !$latitude || !$longitude)
        {
            $error      =   array("error_message" => trans('maps_lib.incomplete_parameters'));
        }
        else
        {
            $lang = isset($lang) ? $lang : $this->sysLang;
            $this->setCountryLang($lang);

            $params         =   array(
                "apiKey"    =>  $this->places_key_api,
                "mode"      =>  "retrieveAddresses",
                "language"  =>  $this->lang,
                "country"   =>  $this->country,
                "maxresults"=>  1,
                "prox"      =>  $latitude.",".$longitude.",250"
            );

            $curl_string    =   $this->reverse_url_api . http_build_query($params);
            $php_obj        =   self::curlCall($curl_string);
            $response_obj   =   json_decode($php_obj);  
        }

        if(is_object($response_obj) && !property_exists($response_obj, 'error') && count($response_obj->Response->View) > 0)
        {
            $processed  =   $this->processGeocodeResponse($response_obj->Response->View[0]);
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
     * Processes the Places curl response and returns a formatted address array.
     * 
     * @param Object      $prediction       A prediction object to be formatted.
     *
     * @return Array      $processed        Formatted array of addresses.
     */
    private function processPlacesResponse($prediction)
    {
        if(property_exists($prediction, 'id')){
            $main_text = $prediction->title;
            $secondary_text = str_replace('<br/>', ' ', $prediction->vicinity);

            $processed['address']        = $main_text . ' ' . $secondary_text;
            $processed['place_id']       = null;
            $processed['latitude']       = $prediction->position[0];
            $processed['longitude']      = $prediction->position[1];
            $processed['main_text']      = $main_text;
            $processed['secondary_text'] = $secondary_text;
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
        if(property_exists($prediction, 'Result') && count($prediction->Result) > 0)
        {
            
            $processed['place_id']      =   null;
            $processed['street_name']   =   isset($prediction->Result[0]->Location->Address->Street) ? $prediction->Result[0]->Location->Address->Street : null;
            $processed['address']       =   isset($prediction->Result[0]->Location->Address->Label) ? $prediction->Result[0]->Location->Address->Label : null ;
            $processed['street_number'] =   isset($prediction->Result[0]->Location->Address->HouseNumber) ? $prediction->Result[0]->Location->Address->HouseNumber : null;
            $processed['postal_code']   =   isset($prediction->Result[0]->Location->Address->PostalCode) ? $prediction->Result[0]->Location->Address->PostalCode : null ;
            $processed['latitude']      =   isset($prediction->Result[0]->Location->DisplayPosition->Latitude) ? $prediction->Result[0]->Location->DisplayPosition->Latitude : null ;
            $processed['longitude']     =   isset($prediction->Result[0]->Location->DisplayPosition->Longitude) ? $prediction->Result[0]->Location->DisplayPosition->Longitude : null;
        }
        else
        {
            $processed  =  false;
        }

        return $processed;
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