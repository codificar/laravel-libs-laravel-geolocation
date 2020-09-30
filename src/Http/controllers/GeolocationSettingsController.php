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

//External Uses


class GeolocationSettingsController extends Controller
{	

	public function create()
	{
		// Category 10			
		$setting = 10;
		$list = GeolocationSettings::where('category', $setting)->get();
		$model = $this->getViewModel($list);
		
		$title = ucwords(trans('customize.Settings'));
	
		// return View::make('gateway_nfe::settings.gateway') 			
		// 	->with('title', $title)
		// 	->with('page', 'settings')
		// 	->with('model', $model);
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