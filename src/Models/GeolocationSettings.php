<?php

namespace Codificar\Geolocation\Models;

//Larvel Uses
use Illuminate\Database\Eloquent\Relations\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Carbon\Carbon;
use Eloquent;

//External Uses
use Settings;
use Config;

class GeolocationSettings extends Settings {
	const GeolocationCategory = 'enum.category.Geolocation';

	/**
	 * Get Geolocation Category
	 *
	 * @return Object
	*/
	public static function getGeolocationCategory (){
		return Config::get(self::GeolocationCategory);		
	}

	/**
	 * Get Data by category
	 *
	 * @return Object
	*/
	public static function getCategoryList (){
		return self::where('category', Config::get(self::GeolocationCategory))->get();		
	}


	/**
	 * Get Unit Distance
	 *
	 * @return string
	*/
	public static function getDefaultDistanceUnit (){
		$settings = self::where('key', 'default_distance_unit')->first();

		if($settings)
		   return $settings->value;
	   else
		   // No entry, returns default km
		   return 0;
	}

	/**
	 * Get Directions API KEY
	 *
	 * @return string
	*/
	public static function getDirectionsKey(){
		$settings = self::where('key', 'directions_key')->first();

		if($settings && $settings->value)
			return $settings->value;
		else 
			return self::getGoogleMapsApiKey() ;
		
	}

	/**
	 * Get Default Directions API kEY
	 *
	 * @return string
	*/

	public static function getGoogleMapsApiKey (){
		$settings = Settings::where('key', 'google_maps_api_key')->first();

		if($settings)
		   return str_replace(" ", "", $settings->value);
	   else
		   return "";
	}

	/** 
	 * Get the app language
	 * 
	 * @return string
	*/
	public static function getLocale(){
		$settings = self::where('key', 'language')->first();

		if($settings)
		   return $settings->value;
	    else
		   return 'pt-br';
	}

	/**
	 * Get provider directions 
	 *
	 * @return string
	 */
	public static function getDirectionsProvider()
	{
        $settings = self::where('key', 'directions_provider')->first();

        if ($settings)
            return $settings->value;
        else
			return false;
	}

	/**
	 * Get provider directions to estimative
	 *
	 * @return string
	 */
	public static function getEstimateDirectionsProvider()
	{
        $settings = self::where('key', 'directions_provider_estimate')->first();

        if ($settings)
            return $settings->value;
        else
			return false;
	}


	/**
	 * Get provider places 
	 *
	 * @return string
	 */
	public static function getPlacesProvider()
	{
        $settings = self::where('key', 'places_provider')->first();

        if ($settings)
            return $settings->value;
        else
			return false;
	}

	/**
	 * Get places provider redundancy
	 *
	 * @return string
	 */
	public static function getPlacesRedundancyProvider()
	{
        $settings = self::where('key', 'places_provider_redundancy')->first();

        if ($settings)
            return $settings->value;            
        else
			return false;
	}

	/**
	 * Get directions provider redundancy
	 *
	 * @return string
	 */
	public static function getDirectionsRedundancyProvider()
	{
        $settings = self::where('key', 'directions_provider_redundancy')->first();

        if ($settings)
            return $settings->value;            
        else
			return false;
	}

	/**
	 * GET KEY API places redundancy
	 *
	 * @return string
	*/
	public static function getPlacesRedundancyKey(){
		$settings = self::where('key', 'places_key_redundancy')->first();

		if($settings && $settings->value)
			return $settings->value;
		else
			return false;
	}

	/**
	 * GET URL API places url
	 *
	 * @return string
	*/
	public static function getPlacesUrl()
	{
        $settings = self::where('key', 'places_url')->first();

        if ($settings)
            return $settings->value;
        else
			return false;
	}

	/**
	 * GET URL API places redundancy
	 *
	 * @return string
	*/
	public static function getPlacesRedundancyUrl()
	{
        $settings = self::where('key', 'places_url_redundancy')->first();

        if ($settings)
            return $settings->value;
        else
			return false;
	}

	/**
	 * GET ID places redundancy
	 *
	 * @return string
	*/
	public static function getPlacesRedundancyApplicationId()
	{
        $settings = self::where('key', 'places_application_id_redundancy')->first();

        if ($settings)
            return $settings->value;            
        else
			return false;		
	}

	/**
	 * GET ID places
	 *
	 * @return string
	*/
	public static function getPlacesApplicationId()
	{
        $settings = self::where('key', 'places_application_id')->first();

        if ($settings)
            return $settings->value;            
        else
			return false;		
	}

	/**
	 * GET ID Directions redundancy
	 *
	 * @return string
	*/
	public static function getDirectionsRedundancyKey(){
		$settings = self::where('key', 'directions_key_redundancy')->first();

		if($settings && $settings->value)
			return $settings->value;
		else
			return false;
	}

	/**
	 * GET URL API directions
	 *
	 * @return string
	 */
	public static function getDirectionsUrl()
	{
        $settings = self::where('key', 'directions_url')->first();

        if ($settings)
            return $settings->value;
        else
			return false;
	}

	/**
	 * GET URL API directions redundancy
	 *
	 * @return string
	 */
	public static function getDirectionsRedundancyUrl()
	{
        $settings = self::where('key', 'directions_url_redundancy')->first();

        if ($settings)
            return $settings->value;
        else
			return false;
	}

	/**
	 * Obtém a regra de redundância de places
	 *
	 * @return void
	 */
	public static function getPlacesRedundancyRule()
	{
        $settings = self::where('key', 'places_redundancy_rule')->first();

        if ($settings)
            return $settings->value;
        else
			return false;
	}

	/**
	 * Obtém a regra de redundância de directions
	 *
	 * @return void
	 */
	public static function getDirectionsRedundancyRule()
	{
        $settings = self::where('key', 'directions_redundancy_rule')->first();

        if ($settings)
            return $settings->value;
        else
			return false;
	}

	public static function getDirectionsGoogleOptimizeRoute()
	{
		$settings = self::where('key', 'directions_google_optimize_route')->first();

		if ($settings)
			return $settings->value;
		else
			return null;
	}

	public static function getMapProvider(){
		$settings = self::where('key', 'maps_provider')->first();

		if($settings)
		   return str_replace(" ", "", $settings->value);
	   else
		   return "";
	}

	 public static function getMapsApiKey(){
		$settings = self::where('key', 'maps_key')->first();

		if($settings)
		   return str_replace(" ", "", $settings->value);
	   else
		   return "";
	}

	/**
	 * Get if is matrix distance enabled
	 * 
	 * @return boolean
	 */
	public static function getIsMatrixDistanceEnabled()
	{
		$settings = self::where('key', 'directions_matrix_distance')->first();

		if ($settings)
			return (boolean) $settings->value;

		return false;
	}

	/**
	 * Get if is matrix distance enabled
	 * 
	 * @return boolean
	 */
	public static function getIsMatrixDistanceRedundancyEnabled()
	{
		$settings = self::where('key', 'directions_matrix_distance_redundancy')->first();

		if ($settings)
			return (boolean) $settings->value;

		return false;
	}

	/**
	 * GET ID Directions redundancy
	 *
	 * @return string
	*/
	public static function getDirectionsEstimateKey(){
		$settings = self::where('key', 'directions_key_estimate')->first();

		if($settings && $settings->value)
			return $settings->value;
		else
			return false;
	}

	/**
	 * GET URL API directions redundancy
	 *
	 * @return string
	 */
	public static function getDirectionsEstimateUrl()
	{
        $settings = self::where('key', 'directions_url_estimate')->first();

        if ($settings)
            return $settings->value;
        else
			return false;
	}

}