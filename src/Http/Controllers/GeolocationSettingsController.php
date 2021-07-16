<?php
namespace Codificar\Geolocation\Http\Controllers;

use App\Http\Controllers\Controller;

//Laravel uses
use View;
use Input;
use Redirect;
use URL;
use Illuminate\Http\Request;

//Internal Uses
use Codificar\Geolocation\Models\{
	ModelObjectSettings,
	ApplicationSettingsViewModel,
	GeolocationSettings
};


use Codificar\Geolocation\Enums\DirectionsEnum;
use Codificar\Geolocation\Enums\PlacesEnum;
use Codificar\Geolocation\Enums\MapsEnum;
//External Uses

class GeolocationSettingsController extends Controller
{	


	public function create()
	{			
		//Settings Env
		$enviroment = 'admin';

		//Get Settings Data
		$list = GeolocationSettings::getCategoryList();		

		// Format Data
		$model = $this->getViewModel($list);
	
		// Get Enum Values
		$enums = array(
			'directions_provider'	=>	DirectionsEnum::DirectionsProvider,
			'places_provider'		=>	PlacesEnum::PlacesProvider,
			'maps_provider'		    =>	MapsEnum::MapsProvider
		);

		// Get Page Title
		$title = ucwords(trans('geolocationTrans::geolocation.settings_title'));

		// Get Places Value
		$placesProvider = GeolocationSettings::getPlacesProvider();
		
		return View::make('geolocation::settings.index')
			->with('enviroment', $enviroment)
			->with('enum', json_encode($enums))
			->with('title', $title)
			->with('page', 'settings')
			->with('placesProvider', $placesProvider)
			->with('model', json_encode($model));
	}

	/**
	 *  Save Or Update NFE Gateway Settings
	 */
	public function store(Request $request)
	{			
		//Get Setting Category Data
		$settingCategory = GeolocationSettings::getGeolocationCategory();
			
		$params = $request->all();
		
		foreach ($params as $key => $value) {
			$settings = GeolocationSettings::where('key', $value['key'])->first();
			
			if($settings){
				$settings->value = $value['value'];				
			}else{
				$settings = new GeolocationSettings;
				$settings->key = $value['key'];
				$settings->value = $value['value'];
				$settings->tool_tip = $value['tool_tip'];
				$settings->page = $value['page'];
				$settings->category = $value['category'];
				$settings->sub_category = $value['sub_category'];
			}		

			$settings->save();
		}

	}

	/**
	* getObjetoSettings -
	*/
	private function getObjetoSettings($category) {
		$list = GeolocationSettings::where('category', Config::get($category))->get();
		return $list;
	}

	private function getViewModel($list)
	{
		$model = new ModelObjectSettings();
		foreach ($list as $item) {
			$modelApplication = new ApplicationSettingsViewModel();
			$modelApplication->id = $item['id'];
			$modelApplication->key = $item['key'];
			$modelApplication->value = $item['value'];
			$modelApplication->tool_tip = $item['tool_tip'];
			$modelApplication->page = $item['page'];
			$modelApplication->category = $item['category'];
			$modelApplication->sub_category = $item['sub_category'];

			$model->{$item['key']} = $modelApplication;			
		}
		
		return $model;
	}
	
}