<?php 

namespace Codificar\Geolocation\Lib;
//Internal Uses
use Codificar\Geolocation\Models\GeolocationSettings;
//Places
use Codificar\Geolocation\Lib\Places\MapsPlacesGoogleLib;
use Codificar\Geolocation\Lib\Places\MapsPlacesFlightMap;
use Codificar\Geolocation\Lib\Places\MapsPlacesBing;

    /**
     * This class define the requisition type of geolocation
     * and provider used
     */
    class MapsFactory
    {
        /** GEOLOCATION PROVIDERS */
        const MAPS_GOOGLE       = 'google_maps';
        const MAPS_BING         = 'bing_maps';
        const MAPS_MAPQUEST     = 'mapquest_maps';
        const MAPS_MAPBOX       = 'mapbox_maps';
        const MAPS_OPENROUTE    = 'openroute_maps';
        const MAPS_PELIAS       = 'pelias_maps';
        const MAPS_ALGOLIA      = 'algolia_maps';
        const MAPS_HERE         = 'here_maps';
        const MAPS_FLIGHT       = 'flight_map';

        /** GETOLOCATION REQUEST TYPES */
        const TYPE_DIRECTIONS   = 'directions';
        const TYPE_PLACES       = 'places';
        const TYPE_MAPS         = 'maps';

        /** GETOLOCATION REDUNDANCY TYPES */
        const REDUNDANCY_PLACES     =   'redundancy_places';
        const REDUNDANCY_DIRECTIONS =   'redundancy_directions';

        /**
         * @var String          $type Requisition type geolocation
         */
        private $type;

        /**
         * @param String        $type Requisition type geolocation
         */
        public function __construct($type)
        {
            $this->type = $type;
        }

        /**
         * @return Object       Library class defined on settings
         */
        public function createMaps()
        {
            if ($this->type == self::TYPE_DIRECTIONS)
            {           
                switch(GeolocationSettings::getDirectionsProvider())
                {
                    case self::MAPS_BING:
                        return(new MapsDirectionsBingLib());
                    case self::MAPS_MAPQUEST:
                        return(new MapsDirectionsMapQuestLib());
                    case self::MAPS_MAPBOX:
                        return(new MapsDirectionsMapBoxLib());
                    case self::MAPS_OPENROUTE:
                        return(new MapsDirectionsOpenRouteLib());
                    case self::MAPS_FLIGHT:
                            return(new MapsDirectionsFlightMap());
                    default:
                        return(new MapsDirectionsGoogleLib());
                }
            }
            else if ($this->type == self::TYPE_PLACES)
            {
                switch(GeolocationSettings::getPlacesProvider())
                {
                    case self::MAPS_PELIAS:
                        return(new MapsPlacesPeliasLib());
                    case self::MAPS_OPENROUTE:
                        return(new MapsPlacesPeliasLib()); // same library
                    case self::MAPS_ALGOLIA:
                        return(new MapsPlacesAlgoliaLib());
                    case self::MAPS_HERE:
                        return(new MapsPlacesHereLib());
                    case self::MAPS_FLIGHT:
                        return(new MapsPlacesFlightMap());
                    case self::MAPS_BING:
                            return(new MapsPlacesBing());
                    default:
                        return(new MapsPlacesGoogleLib());
                }
            }
            else if ($this->type == self::REDUNDANCY_PLACES)
            {
                $placesRedundancyKey = GeolocationSettings::getPlacesRedundancyKey();
                switch(GeolocationSettings::getPlacesRedundancyProvider())
                {
                    case self::MAPS_PELIAS:
                        return(new MapsPlacesPeliasLib(
                            GeolocationSettings::getPlacesRedundancyUrl(),
                            $placesRedundancyKey
                        ));
                    case self::MAPS_OPENROUTE:
                        return(new MapsPlacesPeliasLib(
                            GeolocationSettings::getPlacesRedundancyUrl(),
                            $placesRedundancyKey
                        ));
                    case self::MAPS_ALGOLIA:
                        return(new MapsPlacesAlgoliaLib(
                            $placesRedundancyKey,
                            GeolocationSettings::getPlacesRedundancyApplicationId()
                        ));
                    default:
                        return(new MapsPlacesGoogleLib(
                            $placesRedundancyKey
                        ));
                }
            }
            else if ($this->type == self::REDUNDANCY_DIRECTIONS)
            {               
                $directionsRedundancyKey = GeolocationSettings::getDirectionsRedundancyKey();
                switch(GeolocationSettings::getDirectionsRedundancyProvider())
                {
                    case self::MAPS_BING:
                        return(new MapsDirectionsBingLib(
                            $directionsRedundancyKey
                        ));
                    case self::MAPS_MAPQUEST:
                        return(new MapsDirectionsMapQuestLib(
                            $directionsRedundancyKey
                        ));
                    case self::MAPS_MAPBOX:
                        return(new MapsDirectionsMapBoxLib(
                            $directionsRedundancyKey
                        ));
                    case self::MAPS_OPENROUTE:
                        return(new MapsDirectionsOpenRouteLib(
                            $directionsRedundancyKey,
                            GeolocationSettings::getDirectionsRedundancyUrl()
                        ));
                    default:
                        return(new MapsDirectionsGoogleLib(
                            $directionsRedundancyKey
                        ));
                }
            }
            else
            {
                return false;
            }
        }

    }