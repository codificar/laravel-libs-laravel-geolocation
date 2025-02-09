<?php 

namespace Codificar\Geolocation\Lib\Directions;

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

        /**
         * Gets polyline and estimate route with multiples points by directions API.
         *
         * @param String       $wayPoints         Array with mutiples decimals thats represent the latitude and longitude of the points in the route.
         *
         * @return Array        ['points' => [['lat','lng']['lat','lng']...],'distance_text','duration_text','distance_value','duration_value','partial_distances','partial_durations']
         */
        public function getPolylineAndEstimateWithWayPoints($wayPoints, $optimize = 0);

        /**
         * Gets static map containing the route especified by paht parameter;
         *
         * @param array  $points points in the request's route
         * @param int  $with map width size
         * @param int  $height map height size
         *
         * @return String    url
         */
        public function getStaticMapByPath(array $points, int $width = 249, int $height = 246);
    }