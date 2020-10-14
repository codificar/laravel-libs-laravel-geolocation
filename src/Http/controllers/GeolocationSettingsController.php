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
use Codificar\Geolocation\Models\GeolocationSettings;

use Codificar\Geolocation\Enums\DirectionsEnum;
use Codificar\Geolocation\Enums\PlacesEnum;
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
			'places_provider'		=>	PlacesEnum::PlacesProvider
		);

		// Get Page Title
		$title = ucwords(trans('customize.Settings'));

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
	public function store($request = null)
	{
		
		$settingCategory = 10;
		$first_setting = GeolocationSettings::first();
		
		foreach (($request ? $request : Input::all()) as $key => $item) {
			$temp_setting = GeolocationSettings::find($key);
			if(!$temp_setting)
				$temp_setting =  GeolocationSettings::where('key', '=', $key)->first();
			if ($temp_setting && isset($item)) {
				$temp_setting->value = $item;
				$temp_setting->save();
			} elseif (!is_numeric($key) && $first_setting && isset($item)) {
				$new_setting = new GeolocationSettings();
				$new_setting->key = $key;
				$new_setting->value = $item;
				$new_setting->page = 1;
				$new_setting->category = $settingCategory;
				$new_setting->save();
			}
		}

		if($request)
			return true;

		$alert = array('class' => 'success', 'msg' => trans('dashboard.settings_saved'));

		// return Redirect::to(URL::Route('NfeGatewaySettings'))->with('alert', $alert);
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