<?php 

namespace Codificar\Geocode\Lib;

    interface IMapsDirections
    {
        /**
         * Gets distance by directions API.
         *
         * @param Decimal       $source_lat         Decimal that represents the starting latitude of the request.
         * @param Decimal       $source_long        Decimal that represents the starting longitude of the request.
         * @param Decimal       $dest_lat           Decimal that represents the destination latitude of the request.
         * @param Decimal       $dest_long          Decimal that represents the destination longitude of the request.
         * 
         * @return Array        ['success', 'data' => ['distance']]
         */
        public function getDistanceByDirections($source_lat, $source_long, $dest_lat, $dest_long);

        /**
         * Gets distance and time by directions API.
         *
         * @param Decimal       $source_lat         Decimal that represents the starting latitude of the request.
         * @param Decimal       $source_long        Decimal that represents the starting longitude of the request.
         * @param Decimal       $dest_lat           Decimal that represents the destination latitude of the request.
         * @param Decimal       $dest_long          Decimal that represents the destination longitude of the request.
         * 
         * @return Array        ['success', 'data' => ['distance','time_in_minutes','distance_text','duration_text']]
         */
        public function getDistanceAndTimeByDirections($source_lat, $source_long, $dest_lat, $dest_long);

        /**
         * Gets polyline and estimate route by directions API.
         *
         * @param Decimal       $source_lat         Decimal that represents the starting latitude of the request.
         * @param Decimal       $source_long        Decimal that represents the starting longitude of the request.
         * @param Decimal       $dest_lat           Decimal that represents the destination latitude of the request.
         * @param Decimal       $dest_long          Decimal that represents the destination longitude of the request.
         * 
         * @return Array        ['points' => [['lat','lng']['lat','lng']...],'distance_text','duration_text','distance_value','duration_value']
         */
        public function getPolylineAndEstimateByDirections($source_lat, $source_long, $dest_lat, $dest_long);

        /**
         * Gets polyline and estimate route by adresses in directions API.
         *
         * @param String        $source_address         String that represents the starting address of the request.
         * @param String        $destination_address    String that represents the destination address of the request.
         * @param Decimal       $source_lat             Decimal that represents the starting latitude of the request.
         * @param Decimal       $source_long            Decimal that represents the starting longitude of the request.
         * @param Decimal       $dest_lat               Decimal that represents the destination latitude of the request.
         * @param Decimal       $dest_long              Decimal that represents the destination longitude of the request.
         * 
         * @return Array        ['points' => [['lat','lng']['lat','lng']...],'distance_text','duration_text','distance_value','duration_value']
         */
        public function getPolylineAndEstimateByAddresses($source_address, $destination_address, $source_lat, $source_long, $dest_lat, $dest_long);
    }