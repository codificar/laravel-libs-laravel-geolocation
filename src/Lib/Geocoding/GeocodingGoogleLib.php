<?php

namespace Codificar\Geolocation\Lib\Geocoding;

use Codificar\Geolocation\Lib\Places\Decimal;

class GeocodingGoogleLib extends AbstractGeocoding
{
    /**
     * @var String $url_api URL to access API
     */
    protected $url_api = "https://maps.googleapis.com/maps/api/geocode/";


    /**
     * Defined properties
     */
    public function __construct($placesKey = null){
        parent::__construct($placesKey);
    }

    public function getLatLangFromAddress(string $address, $sessionToken = null): array
    {
        $latLng = [];
        $success = false;
        $error = [];

        if (!$this->url_api || !$this->places_key_api || !$address) {
            $error = [
                "error_message" => trans('geolocationTrans::geolocation.incomplete_parameters')
            ];
        } else {
            $params = array(
                "key" => $this->places_key_api,
                "radius" => 5000,
                "address" => $address,
                "language" => $this->lang,
            );

            $curl_string = $this->url_api . "json?" . http_build_query($params);
            $php_obj = parent::curlCall($curl_string);
            $response_obj = json_decode($php_obj);

            if (isset($response_obj->status) && $response_obj->status == "OK"){
                $latLng = $response_obj->results[0]->geometry->location;
                $success = true;
            }else {
                $error = array("error_message" => trans('geolocationTrans::geolocation.no_data_found'));
                \Log::info(__FUNCTION__ . ":: curl_string = " . print_r($curl_string, 1));

                \Log::info(__FUNCTION__ . ":: response_obj = " . print_r($response_obj, 1));
            }
        }

        $return = array("success" => $success, "data" => $latLng);

        return count($error) ? array_merge($return, $error) : $return;
    }

}