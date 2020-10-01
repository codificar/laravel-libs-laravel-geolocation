<?php 

namespace Codificar\Geocode\Lib;

interface IMapsPlaces
{
    /**
     * Gets list of places by Places API.
     *
     * @param String       $text            String that represents part of andress.
     * @param Decimal      $requester_lat   Decimal that represents the requester latitude.
     * @param Decimal      $requester_lng   Decimal that represents the requester longitude.
     * 
     * @return Array        [
     *                       'success',
     *                       'data' => [
     *                       [
     *                           'main_text',
     *                           'street',
     *                           'number',
     *                           'district',
     *                           'city',
     *                           'state',
     *                           'country',
     *                           'continent',
     *                           'latitude',
     *                           'longitude',
     *                           'address'
     *                       ],
     *                       ...
     *                     ],
     *                      'error_message'
     *                     ]
     */
    public function getAddressByTextWithLatLng($text, $requester_lat, $requester_lng);

    /**
     * Gets geocode attributes with address by API.
     *
     * @param String       $andress         String that represents andress.
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
    public function getGeocodeWithAddress($address, $placeId = null, $lang = null);

    /**
     * Gets attributes with reverse geocode by API.
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
    public function getGeocodeByLatLng($latitude, $longitude);
}