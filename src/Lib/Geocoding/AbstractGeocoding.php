<?php

namespace Codificar\Geolocation\Lib\Geocoding;

use Codificar\Geolocation\Lib\Places\IMapsPlaces;
use Codificar\Geolocation\Models\GeolocationSettings;

abstract class AbstractGeocoding
{

    /**
     * @var String $sysLang Language of the system
     */
    protected $sysLang;

    /**
     * @var String $lang Language used on requests
     */
    protected $lang;

    /**
     * @var String $country Country used on requests
     */
    protected $country;

    /**
     * Defined properties
     */
    public function __construct($placesKey = null)
    {
        if ($placesKey)
            $this->places_key_api = $placesKey;
        else
            $this->places_key_api = GeolocationSettings::getPlacesKey();

        $this->sysLang = GeolocationSettings::getLocale();
        $lang = isset($lang) ? $lang : $this->sysLang;
        $this->setCountryLang($lang);
    }

    /**
     * Setting the country and language received and forcing BCP 47 or ISO 639-1(language) and ccTLD(country).
     *
     * @param String $langString Language received from request.
     *
     * @return String                           Language string in BCP 47 or ISO 639-1 format.
     */
    private function setCountryLang($langString)
    {
        $langString = strtolower($langString);
        switch ($langString) {
            case 'br':
            case 'pt-br':
                $this->lang = 'pt-BR';
                $this->country = 'bra';
                return true;

            case 'ao':
            case 'pt':
            case 'pt-pt':
            case 'pt-ao':
                $this->lang = 'pt-PT';
                $this->country = 'ago';
                return true;

            case 'es':
            case 'es-ar':
            case 'es-co':
            case 'es-es':
            case 'es-mx':
            case 'es-us':
            case 'es-cl':
                $this->lang = 'es';
                $this->country = 'esp';
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
                $this->country = 'usa';
                return true;

            default:
                $this->lang = 'en';
                $this->country = 'usa';
                return true;
        }
    }

    /**
     * Creates and call request by curl client
     *
     * @param String       $curl_string         URL called on curl request.
     *
     * @return Object      $msg_chk             Response on curl request
     */
    protected static function curlCall($curl_string)
    {
        $session = curl_init($curl_string);
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
        $msg_chk = curl_exec($session);

        return $msg_chk;
    }


    abstract public function getLatLangFromAddress(string $address, $sessionToken = null): array;
}