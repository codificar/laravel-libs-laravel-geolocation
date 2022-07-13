<?php

namespace Codificar\Geolocation\Http\Controllers;

use App\Models\Institution;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Routing\Controller;
use stdClass;

use Codificar\Geolocation\Models\{
	ModelObjectSettings,
	ApplicationSettingsViewModel
};

class ConfigController extends Controller {

	/**
	* get_system_settings     Return view of system configuration
	*/
	public function get_system_settings() {
		// Recupera lista de configurações da Aplicação
		$list = $this->getObjetoSettings('enum.category.System');
		// Recupera dados que serão carregados na view
		$model = $this->getViewModel($list);

		// Enums
		$enums = array(
			'default_theme' => Config::get('enum.default_theme')
			,'language' => Config::get('enum.language')
			,'timezone' => Config::get('enum.timezone'));

		$title = ucwords(trans('customize.Settings'));

		return View::make('settings.system')
						->with('enum', $enums)
						->with('title', $title)
						->with('page', 'settings')
						->with('model', $model);
	}

	/**
	* get_application_settings     Return view of application configuration
	*/
	public function get_application_settings() {
		// return json_encode(Settings::findByKey('google_maps_api_key'));
		// Recupera lista de configurações da Aplicação
		$list = $this->getObjetoSettings('enum.category.Application');
		// Recupera dados que serão carregados na view
		$model = $this->getViewModel($list);

		// Enums
		$enums = array(
			'default_distance_unit' 				=> Config::get('enum.default_distance_unit')
			,'request_time_costing_type' 			=> Config::get('enum.request_time_costing_type')
			,'distance_count_on_provider_start' 	=> Config::get('enum.distance_count_on_provider_start')
			,'visible_value_to_provider' 			=> Config::get('enum.visible_value_to_provider')
			,'show_user_register' 					=> Config::get('enum.show_user_register')
			,'show_provider_referral_field' 		=> Config::get('enum.show_provider_referral_field')
			,'show_user_referral_field' 			=> Config::get('enum.show_user_referral_field')
			,'format_number_car' 					=> Config::get('enum.format_number_car'));

		$theme = Theme::first();
		if (isset($theme->id)) {
			$theme = Theme::first();
		} else {
			$theme = array();
		}
		$title = ucwords(trans('customize.Settings'));

		return View::make('settings.application')
						->with('theme', $theme)
						->with('enum', $enums)
						->with('title', $title)
						->with('page', 'settings')
						->with('model', $model);
	}

	/**
	* get_sms_settings     Return view of sms configuration
	*/
	public function get_sms_settings() {
		// Recupera lista de confirações de SMS
		$list = $this->getObjetoSettings('enum.category.SMS');
		// Recupera dados que serão carregados na view
		$model = $this->getViewModel($list);
		// Enums
		$enums = array(
			'sms_notification' => Config::get('enum.sms_notification'));
		$title = ucwords(trans('customize.Settings'));

		return View::make('settings.sms')
						->with('enum', $enums)
						->with('title', $title)
						->with('page', 'settings')
						->with('model', $model);
	}

	/**
	* get_directory_settings     Return view of directory and url configuration
	*/
	public function get_directory_settings() {
		// Recupera lista de confirações de SMS
		$list = $this->getObjetoSettings('enum.category.Directory');
		// Recupera dados que serão carregados na view
		$model = $this->getViewModel($list);
		$title = ucwords(trans('customize.Settings'));

		return View::make('settings.directory')
						->with('title', $title)
						->with('page', 'settings')
						->with('model', $model);
	}

	/**
	* get_email_settings     Return view of e-mail configuration
	*/
	public function get_email_settings() {

		// Recupera lista de confirações de SMS
		$list = $this->getObjetoSettings('enum.category.Email');
		// Recupera dados que serão carregados na view
		$model = $this->getViewModel($list);
		// Enums
		$enums = array(
			'email_provider_services' => Config::get('enum.email_provider_services')
			,'email_notification' => Config::get('enum.email_notification'));
		$title = ucwords(trans('customize.Settings'));

		return View::make('settings.email')
						->with('enum', $enums)
						->with('title', $title)
						->with('page', 'settings')
						->with('model', $model);
	}

	/**
	* get_payment_settings     Return view of payment configuration
	*/
	public function get_payment_settings() {
		// Recupera lista de confirações de SMS
		$list = $this->getObjetoSettings('enum.category.Payment');

		$fields = Settings::where('key', 'max_debt_allowed')
						->orWhere('key', 'show_bank_account_provider_register')
						->orWhere('key', 'show_payment_method_on_accept_request_screen')
						->orWhere('key', 'allow_provider_to_choose_payment_method')
						->get();

		foreach ($fields as $field) {
			$list->push($field);
		}

		// Recupera dados que serão carregados na view
		$model = $this->getViewModel($list);
		// Enums
		$enums = array(
			'default_business_model' 					=> Config::get('enum.default_business_model')
			,'show_user_register' 						=> Config::get('enum.show_user_register')
			,'payments_by_user' 						=> Config::get('enum.payments_by_user')
			,'get_referral_profit_on_card_payment' 		=> Config::get('enum.get_referral_profit_on_card_payment')
			,'get_promotional_profit_on_cash_payment' 	=> Config::get('enum.get_promotional_profit_on_cash_payment')
			,'get_promotional_profit_on_card_payment' 	=> Config::get('enum.get_promotional_profit_on_card_payment')
			,'referral_code_activation' 				=> Config::get('enum.referral_code_activation')
			,'promotional_code_activation' 				=> Config::get('enum.promotional_code_activation')
			,'get_referral_profit_on_cash_payment' 		=> Config::get('enum.get_referral_profit_on_cash_payment')
			,'auto_transfer_provider_payment' 			=> Config::get('enum.auto_transfer_provider_payment')
			,'auto_transfer_schedule_at_after_selected_number_of_days' => Config::get('enum.auto_transfer_schedule_at_after_selected_number_of_days')
			,'provider_transfer_interval' 				=> Config::get('enum.provider_transfer_interval')
			,'provider_transfer_week_day' 				=> Config::get('enum.provider_transfer_week_day')
			,'payment' 									=> Config::get('enum.payment')
			,'boleto'									=> [['value' => 'gerencianet', 'name' => 'Gerencianet']]
			,'stripe_connect' 							=> config('enum.stripe_connect')
			,'stripe_total_split_refund' 				=> config('enum.stripe_total_split_refund')
			,'with_draw_enabled'		 				=> Config::get('enum.with_draw_enabled'));
		$title = ucwords(trans('customize.Settings'));

		$paymentMethods = json_encode(\Settings::getPaymentsArray());
		
		return View::make('settings.payment')
						->with('enum', $enums)
						->with('title', $title)
						->with('page', 'settings')
						->with('model', $model)
						->with('paymentMethods', json_decode($paymentMethods));
	}

	/**
	* get_push_settings     Return view of push configuration
	*/
	public function get_push_settings() {
		// Recupera lista de confirações de SMS
		$list = $this->getObjetoSettings('enum.category.Push');
		// Recupera dados que serão carregados na view
		$model = $this->getViewModel($list);
		// Montando url dos certificados
		$model->customer_certy_url->value = asset_url() . $model->customer_certy_url->value;
		$model->provider_certy_url->value = asset_url() . $model->provider_certy_url->value;

		// Enums
		$enums = array(
			'push_notification' => Config::get('enum.push_notification')
			,'customer_certy_type' => Config::get('enum.customer_certy_type'));
		$title = ucwords(trans('customize.Settings'));

		return View::make('settings.push')
					->with('enum', $enums)
					->with('title', $title)
					->with('page', 'settings')
					->with('model', $model);
	}

	/**
	* get_indication_settings     Return view of indication configuration
	*/
	public function get_indication_settings() {
		// Recupera lista de confirações de Pagamento
		$list = $this->getObjetoSettings('enum.category.Payment');
		// Recupera dados que serão carregados na view
		$model = $this->getViewModel($list);

		$title = ucwords(trans('customize.Settings'));

		return View::make('settings.indication')
						->with('title', $title)
						->with('page', 'settings')
						->with('model', $model);
	}

	/**
	* getEditKeywords
	*/
	public function getEditKeywords() {
		// Recupera lista de confirações de SMS
		$list = $this->getObjetoCustomize('enum.page.Customize');
		// Recupera dados que serão carregados na view
		$model = $this->getViewModel($list);
		// Enums
		$enums = array(
			'generic_keywords_currency' => Config::get('enum.generic_keywords_currency'));

		$success = Input::get('success');
		$icons = Icons::orderBy('icon_name', 'ASC')->get();
		$title = ucwords(trans('customize.Customize')); /* 'Customize' */

		return View::make('config.keywords')
						->with('title', $title)
						->with('page', 'keywords')
						->with('icons', $icons)
						->with('model', $model)
						->with('enum', $enums)
						->with('success', $success);
	}

	/**
	 * Return view of geolocation configuration
	 * 
	 * @return View
	 */
	public function get_geolocation_settings() 
	{
		// Recupera lista de geolocalização
		$list = $this->getObjetoSettings('enum.category.Geolocation');

		// Recupera dados que serão carregados na view
		$model = $this->getViewModel($list);

		// Enums
		$enums = array(
			'directions_provider'			=>	Config::get('enum.directions_provider'),
			'places_provider'				=>	Config::get('enum.places_provider'),
			'refresh_session_deflate_search'=>	Config::get('enum.refresh_session_deflate_search'),
			'sessiontoken_work'				=>	Config::get('enum.sessiontoken_work')
		);

		// Obtém o título da página
		$title = ucwords(trans('customize.Settings'));

		// Obtém o provider de places
		$placesProvider = Settings::getPlacesProvider();

		return View::make('settings.geolocation')
						->with('enum', $enums)
						->with('title', $title)
						->with('page', 'settings')
						->with('placesProvider', $placesProvider)
						->with('model', $model);
	}

	/**
	* save_keywords
	*/
	public function save_keywords() {
		$this->save();
		Session::flash('flashMsg',array('type'=>'success','msg'=>trans('keywords.config_update_alert')));
		return Redirect::to('/admin/edit_keywords?success=1');
	}
	
	/**
	* saveSystemSettings
	*/
	public function saveSystemSettings() {
		// Recupera lista de confirações de SMS
		$listSettings = $this->getObjetoSettings('enum.category.System');
		// Recupera dados que serão carregados na view
		$model = $this->getViewModel($listSettings);
		// Atualiza
		Config::set('app.locale', Input::get($model->language->value));
		\App::setLocale($model->language->value);
		
		$this->save();
		return Redirect::to("/admin/settings/system");
	}

	/**
	* saveApplicationSettings
	*/
	public function saveApplicationSettings() {
		$ps = Theme::all()->count();
		if ($ps == 1) {
			$theme = Theme::first();
		} else {
			$theme = new Theme;
		}

		// Se o arquivo selecionado é Logo.
		if (Input::hasFile('logo')) {
			// Upload File
			$file_name = time();
			$file_name .= rand();
			$ext = Input::file('logo')->getClientOriginalExtension();

			Input::file('logo')->move(public_path() . "/uploads", $file_name . "." . $ext);
			$local_url = $file_name . "." . $ext;

			$this->uploadToS3($file_name, $local_url);

			if (isset($theme->logo)) {
				$logo = asset_url() . '/uploads/' . $theme->logo;
				unlink_image($logo);
			}
			$theme->logo = $local_url;
		}
		// Se o arquivo selecionado é Ícone.
		if (Input::hasFile('icon')) {
			// Upload File
			$file_name = time();
			$file_name .= rand();
			$ext = Input::file('icon')->getClientOriginalExtension();

			Input::file('icon')->move(public_path() . "/uploads", $file_name . "." . $ext);
			$local_url = $file_name . "." . $ext;

			$this->uploadToS3($file_name, $local_url);

			if (isset($theme->favicon)) {
				$icon = asset_url() . '/uploads/' . $theme->favicon;
				unlink_image($icon);
			}
			$theme->favicon = $local_url;
		}
		// Se o arquivo selecionado é uma imagem para o fundo da tela de login do sistema provedor.
		if(Input::hasFile('bckSignupProvider')) {
			// Upload File
			$file_name = time();
			$file_name .= rand();
			$ext = Input::file('bckSignupProvider')->getClientOriginalExtension();

			Input::file('bckSignupProvider')->move(public_path() . "/uploads", $file_name . "." . $ext);
			$local_url = $file_name . "." . $ext;

			$this->uploadToS3($file_name, $local_url);

			if (isset($theme->background_image_provider_signup)) {
				$background_image_provider_signup = asset_url() . '/uploads/' . $theme->background_image_provider_signup;
				unlink_image($background_image_provider_signup);
			}
			$theme->background_image_provider_signup = $local_url;
		}
		// Salva as mídias anexadas
		$theme->save();
		$this->save();
		return Redirect::to("/admin/settings/application");
	}

	/**
	* saveSMSSettings
	*/
	public function saveSMSSettings() {
		$this->save();
		return Redirect::to("/admin/settings/sms");
	}

	/**
	* saveDirectorySettings
	*/
	public function saveDirectorySettings() {
		$this->save();
		return Redirect::to("/admin/settings/directory");
	}

	/**
	* saveDirectorySettings
	*/
	public function saveEmailSettings() {
		$this->save();
		return Redirect::to("/admin/settings/email");
	}

	/**
	* saveIndicationSettings
	*/
	public function saveIndicationSettings() {
		$this->save();
		return Redirect::to("/admin/settings/indication");
	}

	/**
	* saveDirectorySettings
	*/
	public function savePaymentSettings() {

		// Checkboxs da página
		$paymentMethods = \Settings::getPaymentsArray();
		$arrPayment = array();

		foreach($paymentMethods as $payment){
			$arrPayment[$payment['key']]['id'] = $payment['id'];
			$arrPayment[$payment['key']]['value'] = isset($_POST[$payment['key']]) ? 1 : 0;
		}
		
		$paymentDebt = array();
		$paymentDebt['money'] = isset($_POST['payment_money_debt']) ? 1 : 0;
		$paymentDebt['card'] = isset($_POST['payment_card_debt']) ? 1 : 0;
		$paymentDebt['voucher'] = isset($_POST['payment_voucher_debt']) ? 1 : 0;

		$list = $this->recoverLists();

		$array_list = $list['settings']->toArray();
		$key = 'stripe_connect';
		$value = Input::has($key) ? Input::get($key) : 'no_connect';

		$id_stripe_connected = array_search($key, array_column($array_list, 'key'));

		if(is_bool($id_stripe_connected) && !$id_stripe_connected){
			$settings = $this->updateSetting($key, $value, null);
		}

		$list = $this->recoverLists();

		// Percorre array de registros da tabela Settings e atualiza o value de cada registro.
		foreach($list['settings'] as $item) {
			if (!is_null(Input::get($item->id))) {
				$this->savePaySettings($list['model'], $item, $arrPayment, $paymentDebt);
			}
		}
		return Redirect::to("/admin/settings/payment");
	}

	/**
	* savePaySettings
	*/
	public function savePaySettings($model, $item, $arrPayment, $paymentDebt) {
		$temp_setting = Settings::find($item->id);
		if($item->id == $model->provider_transfer_interval->id) {
			if(Input::get($item->id) == 'weekly') {
				$temp_setting->value = Input::get($item->id);
				$temp_setting_ptd = Settings::find($model->provider_transfer_day->id);
				$temp_setting_ptd->value = Input::get($model->provider_transfer_week_day->id);
				$temp_setting_ptd->save();
			}
			else {
				$temp_setting_ptd = Settings::find($model->provider_transfer_day->id);
				$temp_setting_ptd->value = Input::get($model->provider_transfer_day->id);
				$temp_setting_ptd->save();
			}
			$temp_setting->value = Input::get($item->id);
		} else if($item->id != $model->provider_transfer_day->id) {

			if($item->id == $model->payment_money_debt->id)
				$temp_setting->value = $paymentDebt['money'];
			else if($item->id == $model->payment_card_debt->id)
				$temp_setting->value = $paymentDebt['card'];
			else if($item->id == $model->payment_voucher_debt->id)
				$temp_setting->value = $paymentDebt['voucher'];
			else
				$temp_setting->value = Input::get($item->id);

			foreach($arrPayment as $payment){
				if($item->id == $payment['id']){					
					$temp_setting->value = $payment['value'];
				}
			}			
		}

		$temp_setting->save();
		return $temp_setting;
	}

	/**
	 * saveGeolocationSettings 	Save Geolocation parameters on settings
	 */
	public function saveGeolocationSettings($api)
	{
		/* COMMON FIELDS */
		$input[$api.'_provider']	=	Input::get($api.'_provider');
		$input[$api.'_key']			=	trim(Input::get($api.'_key'));
		$input[$api.'_url']			=	trim(Input::get($api.'_url'));
		$redundancy_rule			=	0;
		$error_messages				=	array();
		$urlRule = $input[$api.'_provider'] == "openroute_maps" || $input[$api.'_provider'] == "pelias_maps" ? "required" : "";

		if(Input::has($api.'_redundancy_rule')){
			$redundancy_rule = trim(Input::get($api.'_redundancy_rule'));
			$input[$api.'_redundancy_rule'] = $redundancy_rule;
		}
		
		/* REDUNDANCY FIELDS */
		if($redundancy_rule == 1){
			$input[$api.'_provider_redundancy']			=	Input::get($api.'_provider_redundancy');
			$input[$api.'_key_redundancy']				=	trim(Input::get($api.'_key_redundancy'));
			$input[$api.'_url_redundancy']				=	trim(Input::get($api.'_url_redundancy'));

			$urlRedundancyRule = ($input[$api.'_provider_redundancy'] == "openroute_maps" || $input[$api.'_provider_redundancy'] == "pelias_maps") && $api == "places" ? "required" : "";
		}

		/* APPLICATION ID RULES */
		if($api == "places"){
			$input[$api.'_application_id']			=	trim(Input::get($api.'_application_id'));

			$applicationIdRule = $input[$api.'_provider'] == "algolia_maps" && $api == "places" ? "required" : "";

			$validatorAplicationId	=	Validator::make(
				array(
					$api.'_application_id'			=>	$input[$api.'_application_id']
				), array(
					$api.'_application_id'			=>	$applicationIdRule
				), array(
					$api.'_application_id.required'	=>	trans('validation.required', [ 'attribute' => trans('setting.geolocation_application_id') ])
				)
			);
			$error_messages = $validatorAplicationId->messages()->all();
		}

		/* COMMON VALIDATION */
		$validatorGeolocation	=	Validator::make(
			array(
				$api.'_provider'					=>	$input[$api.'_provider'],
				$api.'_key'							=>	$input[$api.'_key'],
				$api.'_url'							=>	$input[$api.'_url']
			), array(
				$api.'_provider'					=>	'required',
				$api.'_key'							=>	'required',
				$api.'_url'							=>	$urlRule
			), array(
				$api.'_provider.required' 	    => trans('validation.required', [ 'attribute' => trans('setting.geolocation_provider') ]),
				$api.'_key.required' 		    => trans('validation.required', [ 'attribute' => trans('setting.geolocation_key') ]),
				$api.'_url.required' 		    => trans('validation.required', [ 'attribute' => trans('setting.geolocation_url') ])
			)
		);

		$error_messages = array_merge(
			$validatorGeolocation->messages()->all(),
			$error_messages
		);

		/* REDUNDANCY VALIDATION */
		if ($redundancy_rule == 1){
			$validatorGeolocationRedundancy	= Validator::make(
				array(
					$api.'_provider_redundancy'			=>	$input[$api.'_provider_redundancy'],
					$api.'_key_redundancy'				=>	$input[$api.'_key_redundancy'],
					$api.'_url_redundancy'				=>	$input[$api.'_url_redundancy']
				), array(
					$api.'_provider_redundancy'			=>	"required",
					$api.'_key_redundancy'				=>	"required",
					$api.'_url_redundancy'				=>	$urlRedundancyRule
				), array(
					$api.'_provider_redundancy.required' 	    => trans('validation.required', [ 'attribute' => trans('setting.provider_redundancy') ]),
					$api.'_key_redundancy.required' 		    => trans('validation.required', [ 'attribute' => trans('setting.key_redundancy') ]),
					$api.'_url_redundancy.required' 		    => trans('validation.required', [ 'attribute' => trans('setting.url_redundancy') ])
				)
			);

			/* APPLICATION ID VALIDATION */
			if($api == "places"){
				$input[$api.'_application_id_redundancy']	=	trim(Input::get($api.'_application_id_redundancy'));
				$applicationIdRedundancyRule = $input[$api.'_provider_redundancy'] == "algolia_maps" && $api == "places" ? "required" : "";

				$validatorAppIdRedundancy = Validator::make(
					array(
						$api.'_application_id_redundancy'	=>	$input[$api.'_application_id_redundancy']
					), array(
						$api.'_application_id_redundancy'	=>	$applicationIdRedundancyRule
					), array(
						$api.'_application_id_redundancy.required'	=> trans('validation.required',
							[
								'attribute' => trans('setting.application_id_redundancy') 
							]
						)
					)
				);

				$error_messages = array_merge(
					$validatorAppIdRedundancy->messages()->all(),
					$error_messages
				);
			}

			$validatorGeolocationRedundancy->after(function ($validatorGeolocationRedundancy) use ($api,$input) {
				if ($input[$api.'_provider'] == $input[$api.'_provider_redundancy']) {
					$validatorGeolocationRedundancy->errors()->add($api.'_provider_redundancy', trans('setting.provider_cannot_equals'));
				}
			});

			$error_messages = array_merge(
				$validatorGeolocationRedundancy->messages()->all(),
				$error_messages
			);
		}

		if(count($error_messages) > 0){
			return Redirect::back()->withErrors($error_messages);
		}

		// Remove a barra final na URL se existir para Pelias
		if ($input[$api.'_provider'] == "pelias_maps" && strlen($input[$api.'_url']) > 0) {
			while (substr($input[$api.'_url'], -1) == "/") {
				$input[$api.'_url'] = substr_replace($input[$api.'_url'], "", -1);
			}
		}
		if (isset($input[$api.'_provider_redundancy']) && $input[$api.'_provider_redundancy'] == "pelias_maps" && strlen($input[$api.'_url_redundancy']) > 0) {
			while (substr($input[$api.'_url_redundancy'], -1) == "/") {
				$input[$api.'_url_redundancy'] = substr_replace($input[$api.'_url_redundancy'], "", -1);
			}
		}

		$updateErrors = Settings::updateGeolocationSettings($input);

		if (count($updateErrors) > 0) {
			return Redirect::back()->withErrors($updateErrors);
		} else{
			return Redirect::back()->with('success', trans('setting.geolocation_success'));
		}
	}

	/**
	* addSetting
	*/
	public function updateSetting($key, $value, $settings) {
		$settings = is_null($settings) ? new Settings : $settings;
		$settings->key = $key;
		$settings->value = $value;
		$settings->page = 1;
		$settings->category = 6;
		$settings->sub_category = 0;
		$settings->save();
		return $settings;
	}

	/**
	* recoverLists
	*/
	public function recoverLists() {
		// Recupera lista de confirações de SMS
		$listSettings = $this->getObjetoSettings('enum.category.Payment');
		// Recupera dados que serão carregados na view
		$list['model'] = $this->getViewModel($listSettings);
		$list['settings'] = Settings::all();

		return $list;
	}

	/**
	* savePushSettings
	*/
	public function savePushSettings() {
		$is_file_cert_user = false;
		$is_file_cert_provider = false;

		// Se o arquivo selecionado é Logo.
		if (Input::hasFile('cert_user')) {
			// Upload File
			$file_name = 'Client_certy';
			$ext = Input::file('cert_user')->getClientOriginalExtension();
			if($ext != 'pem'){
				echo ("<script>alert('Por favor, carregue o certificado no foramto .pem;');</script>");
			}
			else {
				Input::file('cert_user')->move(base_path()."/config/iosCertificates/", $file_name . "." . $ext);
				$local_url_cert_user = "config/iosCertificates/" . $file_name . "." . $ext;
				$is_file_cert_user = true;
			}
		}

		if (Input::hasFile('cert_provider')) {
			// Upload File
			$file_name = 'Client_certy';
			$ext = Input::file('cert_provider')->getClientOriginalExtension();
			if($ext != 'pem'){
				echo ("<script>alert('Por favor, carregue o certificado no foramto .pem;');</script>");
			}
			else {
				Input::file('cert_provider')->move(public_path() . "/apps/ios_push/provider/iph_cert/", $file_name . "." . $ext);
				$local_url_cert_provider = "/apps/ios_push/provider/iph_cert/" . $file_name . "." . $ext;
				$is_file_cert_provider = true;
			}
		}

		// adiciona o audio do push
		$this->addAudioPush();

		// Recupera lista de confirações de SMS
		$listSettings = $this->getObjetoSettings('enum.category.Push');
		// Recupera dados que serão carregados na view
		$model = $this->getViewModel($listSettings);
		$list = Settings::all();

		// Percorre array de registros da tabela Settings e atualiza o value de cada registro.
		foreach($list as $item) {
			if (!is_null(Input::get($item->id))){
				$temp_setting = Settings::find($item->id);
				$temp_setting->value = Input::get($item->id);
			}
			else {
				$temp_setting = Settings::find($item->id);
				if($item->id == $model->customer_certy_url->id && $is_file_cert_user)
					$temp_setting->value = $local_url_cert_user;
				else if($item->id == $model->provider_certy_url->id && $is_file_cert_provider)
					$temp_setting->value = $local_url_cert_provider;
			}
			$temp_setting->save();
		}
		return Redirect::to("/admin/settings/push");
	}
	
	/**
	* addAudioPush do ConfigController
	*/
	protected function addAudioPush() {
		if (Input::hasFile('audio_push')) {
			// Upload File
			$file_name = 'audio_' . sha1(time());
			$ext = Input::file('audio_push')->getClientOriginalExtension();

			if ($ext == "wav" || $ext == "WAV") {
				Input::file('audio_push')->move(public_path() . "/apps/audio", $file_name . "." . $ext);
				$local_url = $file_name . "." . $ext;

				// salva no s3 se for o caso
				$this->uploadToS3($file_name, $local_url);

				$audio_url = asset_url() . '/apps/audio/' . $local_url;

				///salvar url no banco de dados.
				Settings::setAudioUrl($audio_url);

				return Redirect::to('/admin/settings/installation?success=1');

			} else {
				return Redirect::to('/admin/settings/installation?success=7');
			}
		}

		return Redirect::to('/admin/settings/installation');
	}
	
	/**
	* uploadToS3
	*/
	public function uploadToS3($file_name, $local_url) {
		// Upload to S3
		return upload_to_s3($file_name, $local_url);
	}

	/**
	* Save -
	*/
	private function save() {
		$list = Settings::all();
		// Percorre array de registros da tabela Settings e atualiza o value de cada registro.
		foreach($list as $item) {
			if (!is_null(Input::get($item->id))){
				$temp_setting = Settings::find($item->id);
				$temp_setting->value = Input::get($item->id);
				$temp_setting->save();
			}
		}
	}

	/**
	* Save - getViewModel
	*/
	private function getViewModel($list) {
		$model = new ModelObjectSettings();
		foreach($list as $item) {
			$modelApplication = new ApplicationSettingsViewModel();
			$modelApplication->id = $item['id'];
			$modelApplication->key = $item['key'];
			$modelApplication->value = $item['value'];
			$modelApplication->tool_tip = $item['tool_tip'];
			$modelApplication->page = $item['page'];
			$modelApplication->category = $item['category'];
			$modelApplication->sub_category = $item['sub_category'];
            $modelApplication->shortest_distance = $item['shortest_distance'];

			$model->{$item['key']} = $modelApplication;
		}
		return $model;
	}

	/**
	* getObjetoSettings -
	*/
	private function getObjetoSettings($category) {
		$list = Settings::where('category', Config::get($category))->get();
		return $list;
	}

	/**
	* getObjetoSettings -
	*/
	private function getObjetoCustomize($category) {
		$list = Settings::where('page', Config::get($category))->get();
		return $list;
	}

	public function import() {

		// Enums
		$enums = array(
			'category' => Config::get('enum.category'));

		return View::make('settings.import')
				->with('title', "Importar")
				->with('page', "")
				->with('enum', $enums);
	}
	
	public function save_settings() {

		if(Input::has('car_number_format')){
			$car_number_key = Settings::where('key', 'car_number_format')->first();
			$car_number_key->value = Input::get('car_number_format');
			$car_number_key->save();
		}

		$settings = Settings::all();
		foreach ($settings as $setting) {
			if (Input::get($setting->id) != NULL) {
				$temp_setting = Settings::find($setting->id);
				if(($temp_setting->key != "google_maps_api_key") && ($temp_setting->key != "car_number_format"))
					$temp_setting->value = Input::get($setting->id);
				$temp_setting->save();
			}
		}
		return Redirect::to('/admin/settings?success=1');
	}

	//Installation Settings
	public function installation_settings() {
		$twillo_account_sid = Settings::findByKey('twillo_account_sid');
		$twillo_auth_token = Settings::findByKey('twillo_auth_token');
		$twillo_number = Settings::findByKey('twillo_number');
		$timezone = Settings::findByKey('timezone');

		$url = Config::get('general.site_url');
		$website_title = Settings::findByKey('website_title');
		$s3_bucket = Settings::findByKey('s3_bucket');

		//CONFIGURACOES DE E-MAIL
		$host = Config::get('mail.host');
		$mail_driver = Config::get('mail.driver');
		$email_name = Config::get('mail.from.name');
		$email_address = Config::get('mail.from.address');
		$mandrill_secret = Config::get('services.mandrill.secret');
		$sendgrid_secret = Config::get('services.sendgrid.secret');
		$mandrill_username = Config::get('services.mandrill.username');
		$sendgrid_username = Config::get('services.sendgrid.username');

		//CONFIGURACOES DE PAGAMENTO
		//modelo de negócios
		$settingBusinessModel = Settings::where('key', 'default_business_model')->first();
		$settingsProviderTransferInterval = Settings::where('key', 'provider_transfer_interval')->first();
		$settingsProviderTransferDay = Settings::where('key', 'provider_transfer_day')->first();

		//tipos de pagamento
		$settingMoney = Settings::where('key', 'payment_money')->first();
		$settingCard = Settings::where('key', 'payment_card')->first();
		$settingVoucher = Settings::where('key', 'payment_voucher')->first();
		$settingPaymentsByUser = Settings::where('key', 'payments_by_user')->first();

		//intermediadores de pagamento
		$default_payment = Settings::findByKey('default_payment');

		//braintree
		$braintree_environment = Settings::findByKey('braintree_environment');
		$braintree_merchant_id = Settings::findByKey('braintree_merchant_id');
		$braintree_public_key = Settings::findByKey('braintree_public_key');
		$braintree_private_key = Settings::findByKey('braintree_private_key');
		$braintree_cse = Settings::findByKey('braintree_cse');

		//stripe
		$stripe_secret_key = Settings::findByKey('stripe_secret_key');
		$stripe_publishable_key = Settings::findByKey('stripe_publishable_key');

		//pagar.me
		$pagarme_api_key = Settings::findByKey('pagarme_api_key');
		$pagarme_encryption_key = Settings::findByKey('pagarme_encryption_key');
		$pagarme_recipient_id = Settings::findByKey('pagarme_recipient_id');

		// byebnk
		$byebnk_api_key = Settings::findByKey('byebnk_api_key');		
		$byebnk_api_user = Settings::findByKey('byebnk_api_user');		

		/* DEVICE PUSH NOTIFICATION DETAILS */
		$customer_certy_url = asset_url() . Settings::findByKey('customer_certy_url');
		$customer_certy_pass = Settings::findByKey('customer_certy_pass');
		$customer_certy_type = Settings::findByKey('customer_certy_type');
		$provider_certy_url = asset_url() . Settings::findByKey('provider_certy_url');
		$provider_certy_pass = Settings::findByKey('provider_certy_pass');
		$provider_certy_type = Settings::findByKey('provider_certy_type');
		$gcm_browser_key = Settings::findByKey('gcm_browser_key');
		/* DEVICE PUSH NOTIFICATION DETAILS END */

		$install = array(
			'braintree_environment' => $braintree_environment,
			'braintree_merchant_id' => $braintree_merchant_id,
			'braintree_public_key' => $braintree_public_key,
			'braintree_private_key' => $braintree_private_key,
			'braintree_cse' => $braintree_cse,
			'twillo_account_sid' => $twillo_account_sid,
			'twillo_auth_token' => $twillo_auth_token,
			'twillo_number' => $twillo_number,

			'stripe_publishable_key' => $stripe_publishable_key,
			'stripe_secret_key' => $stripe_secret_key,
			'pagarme_api_key' => $pagarme_api_key,
			'pagarme_encryption_key' => $pagarme_encryption_key,
			'pagarme_recipient_id' => $pagarme_recipient_id,
			'byebnk_api_key' => $byebnk_api_key,
			'byebnk_api_user' => $byebnk_api_user,

			'mail_driver' => $mail_driver,
			'email_address' => $email_address,
			'mandrill_username' => $mandrill_username,
			'sendgrid_username' => $sendgrid_username,
			'email_name' => $email_name,
			'host' => $host,
			'mandrill_secret' => $mandrill_secret,

			'sendgrid_secret' => $sendgrid_secret,

			'default_business_model' => $settingBusinessModel ? $settingBusinessModel->value : 'monthly',
			'provider_transfer_interval' => $settingsProviderTransferInterval ? $settingsProviderTransferInterval->value : "daily",
			'provider_transfer_day' => $settingsProviderTransferDay ? $settingsProviderTransferDay->value : "1",

			'payment_money' => $settingMoney? $settingMoney->value : '1',
			'payment_card' => $settingCard? $settingCard->value : '1',
			'payment_voucher' => $settingVoucher? $settingVoucher->value : '1',
			'payments_by_user' => $settingPaymentsByUser? $settingPaymentsByUser->value : '1',

			'default_payment' => $default_payment,
			/* DEVICE PUSH NOTIFICATION DETAILS */
			'customer_certy_url' => $customer_certy_url,
			'customer_certy_pass' => $customer_certy_pass,
			'customer_certy_type' => $customer_certy_type,
			'provider_certy_url' => $provider_certy_url,
			'provider_certy_pass' => $provider_certy_pass,
			'provider_certy_type' => $provider_certy_type,
			'gcm_browser_key' => $gcm_browser_key,
				/* DEVICE PUSH NOTIFICATION DETAILS END */
		);
		$success = Input::get('success');
		$cert_def = 0;
		$cer = Certificates::where('file_type', 'certificate')->where('client', 'apple')->get();
		foreach ($cer as $key) {
			if ($key->default == 1) {
				$cert_def = $key->type;
			}
		}
		$title = ucwords(trans('adminController.install_of'). " " . trans('customize.Settings')); /* 'Installation Settings' */
		return View::make('install_settings')
						->with('title', $title)
						->with('success', $success)
						->with('page', 'settings')
						->with('cert_def', $cert_def)
						->with('install', $install);
	}

	public function finish_install() {
		$braintree_cse = $byebnk_api_key = $byebnk_api_user = $pagarme_api_key = $pagarme_encryption_key = $pagarme_recipient_id = $stripe_publishable_key = $url = $timezone = $website_title = $s3_bucket = $twillo_account_sid = $twillo_auth_token = $twillo_number = $default_payment = $stripe_secret_key = $braintree_environment = $braintree_merchant_id = $braintree_public_key = $braintree_private_key = $customer_certy_url = $customer_certy_pass = $customer_certy_type = $provider_certy_url = $provider_certy_pass = $provider_certy_type = $gcm_browser_key = $key_provider = $key_user = $key_taxi = $key_trip = $key_currency = $total_trip = $cancelled_trip = $total_payment = $completed_trip = $card_payment = $credit_payment = $settingPaymentsByUser = $key_ref_pre = $android_client_app_url = $android_provider_app_url = $ios_client_app_url = $ios_provider_app_url = NULL;

		//CONFIGURACOES DE PAGAMENTO

		//modelo de negócios
		$settingBusinessModel = Settings::where('key', 'default_business_model')->first();
		$settingsProviderTransferInterval = Settings::where('key', 'provider_transfer_interval')->first();
		$settingsProviderTransferDay = Settings::where('key', 'provider_transfer_day')->first();

		if(!$settingBusinessModel){
			$settingBusinessModel = new Settings();
			$settingBusinessModel->key = 'default_business_model';
		}

		if(!$settingsProviderTransferInterval){
			$settingsProviderTransferInterval = new Settings();
			$settingsProviderTransferInterval->key = 'provider_transfer_interval';
		}

		if(!$settingsProviderTransferDay){
			$settingsProviderTransferDay = new Settings();
			$settingsProviderTransferDay->key = 'provider_transfer_day';
		}

		//opções de pagamento
		$settingMoney = Settings::where('key', 'payment_money')->first();
		$settingCard = Settings::where('key', 'payment_card')->first();
		$settingVoucher = Settings::where('key', 'payment_voucher')->first();

		if(!$settingMoney){
			$settingMoney = new Settings();
			$settingMoney->key = 'payment_money';
		}
		if(!$settingCard){
			$settingCard = new Settings();
			$settingCard->key = 'payment_card';
		}
		if(!$settingVoucher){
			$settingVoucher = new Settings();
			$settingVoucher->key = 'payment_voucher';
		}

		//Tipos de pagamento por usuario
		$settingPaymentsByUser = Settings::where('key', 'payments_by_user')->first();

		if(!$settingPaymentsByUser){
			$settingPaymentsByUser = new Settings();
			$settingPaymentsByUser->key = 'payments_by_user';
		}

		//OBTER CONFIGURAÇÔES DO ARQUIVO app.php

		//intermediador de pagamento
		$default_payment = Settings::findByKey('default_payment');
		//pagarme
		$pagarme_api_key = Settings::findByKey('pagarme_api_key');
		$pagarme_encryption_key = Settings::findByKey('pagarme_encryption_key');
		$pagarme_recipient_id = Settings::findByKey('pagarme_recipient_id');

		// byebnk
		$byebnk_api_key = Settings::findByKey('byebnk_api_key');
		$byebnk_api_user = Settings::findByKey('byebnk_api_user');

		//stripe
		$stripe_publishable_key = Settings::findByKey('stripe_publishable_key');
		$stripe_secret_key = Settings::findByKey('stripe_secret_key');

		//braintree
		$braintree_environment = Settings::findByKey('braintree_environment');
		$braintree_merchant_id = Settings::findByKey('braintree_merchant_id');
		$braintree_public_key = Settings::findByKey('braintree_public_key');
		$braintree_private_key = Settings::findByKey('braintree_private_key');
		$braintree_cse = Settings::findByKey('braintree_cse');

		$twillo_account_sid = Settings::findByKey('twillo_account_sid');
		$twillo_auth_token = Settings::findByKey('twillo_auth_token');
		$twillo_number = Settings::findByKey('twillo_number');
		$timezone = Settings::findByKey('timezone');

		$url = Config::get('general.site_url');
		$website_title = Settings::findByKey('website_title');
		$s3_bucket = Settings::findByKey('s3_bucket');

		$mail_driver = Config::get('mail.driver');
		$email_name = Config::get('mail.from.name');
		$email_address = Config::get('mail.from.address');
		$mandrill_secret = Config::get('services.mandrill.secret');
		$sendgrid_secret = Config::get('services.sendgrid.secret');

		$host = Config::get('mail.host');

		//DEVICE PUSH NOTIFICATION DETAILS
		$customer_certy_url 	= asset_url() . Settings::findByKey('customer_certy_url');
		$customer_certy_pass 	= Settings::findByKey('customer_certy_pass');
		$customer_certy_type 	= Settings::findByKey('customer_certy_type');
		$provider_certy_url 	= asset_url() . Settings::findByKey('provider_certy_url');
		$provider_certy_pass 	= Settings::findByKey('provider_certy_pass');
		$provider_certy_type 	= Settings::findByKey('provider_certy_type');
		$gcm_browser_key 		= Settings::findByKey('gcm_browser_key');

		if (isset($_POST['mail'])) {

			$mail_driver = Input::get('mail_driver');
			$email_name = Input::get('email_name');
			$email_address = Input::get('email_address');

			$mandrill_secret = Input::get('mandrill_secret');
			$sendgrid_secret = Input::get('sendgrid_secret');
			$mandrill_hostname = "";
			if ($mail_driver == 'mail') {
				$mandrill_hostname = "localhost";
			} elseif ($mail_driver == 'mandrill') {
				$mandrill_hostname = Input::get('host_name');
			}elseif($mail_driver == 'sendgrid'){
				$mandrill_hostname = Input::get('host_name');
			}
			$mailfile = fopen(app_path() . "/config/mail.php", "w") or die(trans('adminController.not_open_file'));
			$mailfile_config = generate_mail_config($mandrill_hostname, $mail_driver, $email_name, $email_address);
			fwrite($mailfile, $mailfile_config);
			fclose($mailfile);

			if ($mail_driver == 'mandrill') {
				$mandrill_username = Input::get('user_name');
				$sendgrid_username = Input::get('user_name2');
				$servicesfile = fopen(app_path() . "/config/services.php", "w") or die(trans('adminController.not_open_file'));
				$servicesfile_config = generate_services_config($mandrill_secret, $mandrill_username, $sendgrid_secret, $sendgrid_username);
				fwrite($servicesfile, $servicesfile_config);
				fclose($servicesfile);
			} else if ($mail_driver == 'sendgrid') {
				$sendgrid_username = Input::get('user_name2');
				$mandrill_username = Input::get('user_name');
				$servicesfile = fopen(app_path() . "/config/services.php", "w") or die(trans('adminController.not_open_file'));
				$servicesfile_config = generate_services_config($mandrill_secret, $mandrill_username, $sendgrid_secret, $sendgrid_username);
				fwrite($servicesfile, $servicesfile_config);
				fclose($servicesfile);
			}
		}
		else{

			if (isset($_POST['sms'])) {
				$twillo_account_sid = Input::get('twillo_account_sid');
				$twillo_auth_token = Input::get('twillo_auth_token');
				$twillo_number = Input::get('twillo_number');
			}

			else if (isset($_POST['payment'])) {

				if($settingBusinessModel->value != trim(Input::get('default_business_model'))){
						$settingBusinessModel->value = trim(Input::get('default_business_model'));
						$settingBusinessModel->save();
				}

				if($settingsProviderTransferInterval->value != trim(Input::get('provider_transfer_interval'))){
						$settingsProviderTransferInterval->value = trim(Input::get('provider_transfer_interval'));
						$settingsProviderTransferInterval->save();
				}

				$providerTransferDay = trim(Input::get('provider_transfer_day_weekly'));
				if($settingsProviderTransferDay->value != $providerTransferDay){
						$settingsProviderTransferDay->value = $providerTransferDay;
						$settingsProviderTransferDay->save();
				}

				if(Input::has('payment_money')){
					$settingMoney->value = '1';
				}
				else{
					$settingMoney->value = '0';
				}
				$settingMoney->save();

				if(Input::has('payment_card')){
					$settingCard->value = '1';
				}
				else{
					$settingCard->value = '0';
				}
				$settingCard->save();

				if(Input::has('payment_voucher')){
					$settingVoucher->value = '1';
				}
				else{
					$settingVoucher->value = '0';
				}
				$settingVoucher->save();

				if(Input::has('payments_by_user')){
					$settingPaymentsByUser->value = Input::get('payments_by_user');
				}
				else{
					$settingPaymentsByUsers->value = '0';
				}
				$settingPaymentsByUser->save();


				$default_payment = Input::get('default_payment');

				$pagarme_api_key = '';
				$pagarme_encryption_key = '';
				$pagarme_recipient_id = '';
				$stripe_secret_key = '';
				$stripe_publishable_key = '';
				$braintree_environment = '';
				$braintree_merchant_id = '';
				$braintree_public_key = '';
				$braintree_private_key = '';
				$braintree_cse = '';
				$byebnk_api_key = '';
				$byebnk_api_user = '';				

				switch($default_payment)
				{
					case 'pagarme':
						$pagarme_api_key = Input::get('pagarme_api_key');
						$pagarme_encryption_key = Input::get('pagarme_encryption_key');
						$pagarme_recipient_id = Input::get('pagarme_recipient_id');
					break;

					case 'byebnk':
						$byebnk_api_key = Input::get('byebnk_api_key');
						$byebnk_api_user = Input::get('byebnk_api_user');
					break;
					
					case 'stripe':
						if ($stripe_secret_key != trim(Input::get('stripe_secret_key')) || $stripe_publishable_key != trim(Input::get('stripe_publishable_key'))) {
							/* DELETE CUSTOMER CARDS FROM DATABASE */
							$delete_un_rq = DB::delete("DELETE FROM payment WHERE 1;");
							/* DELETE CUSTOMER CARDS FROM DATABASE END */

							$stripe_secret_key = Input::get('stripe_secret_key');
							$stripe_publishable_key = Input::get('stripe_publishable_key');						
						}
					break;

					default:
						if ($braintree_environment != trim(Input::get('braintree_environment')) || $braintree_merchant_id != trim(Input::get('braintree_merchant_id')) || $braintree_public_key != trim(Input::get('braintree_public_key')) || $braintree_private_key != trim(Input::get('braintree_private_key')) || $braintree_cse != trim(Input::get('braintree_cse'))) {
							/* DELETE CUSTOMER CARDS FROM DATABASE */
							$delete_un_rq = DB::delete("DELETE FROM payment WHERE 1;");
							/* DELETE CUSTOMER CARDS FROM DATABASE END */

							$braintree_environment = Input::get('braintree_environment');
							$braintree_merchant_id = Input::get('braintree_merchant_id');
							$braintree_public_key = Input::get('braintree_public_key');
							$braintree_private_key = Input::get('braintree_private_key');
							$braintree_cse = Input::get('braintree_cse');							
						}
						break;
				}
			}

			//SALVAR ARQUIVO DE CONFIGURAÇÂO
			$appfile = fopen(app_path() . "/config/app.php", "w") or die(trans('adminController.not_open_file'));

			$appfile_config = generate_app_config($pagarme_api_key, $pagarme_encryption_key, $pagarme_recipient_id, $braintree_cse, $stripe_publishable_key, $url, $timezone, $website_title, $s3_bucket, $twillo_account_sid, $twillo_auth_token, $twillo_number, $default_payment, $stripe_secret_key, $braintree_environment, $braintree_merchant_id, $braintree_public_key, $braintree_private_key, $customer_certy_url, $customer_certy_pass, $customer_certy_type, $provider_certy_url, $provider_certy_pass, $provider_certy_type, $gcm_browser_key, $key_provider, $key_user, $key_taxi, $key_trip, $key_currency, $total_trip, $cancelled_trip, $total_payment, $completed_trip, $card_payment, $credit_payment, $key_ref_pre, $android_client_app_url, $android_provider_app_url, $ios_client_app_url, $ios_provider_app_url);
			fwrite($appfile, $appfile_config);
			fclose($appfile);
		}

		$install = array(
			'pagarme_api_key' => $pagarme_api_key,
			'pagarme_encryption_key' => $pagarme_encryption_key,
			'pagarme_recipient_id' => $pagarme_recipient_id,
			'braintree_environment' => $braintree_environment,
			'byebnk_api_key' => $byebnk_api_key,
			'byebnk_api_user' => $byebnk_api_user,
			'braintree_merchant_id' => $braintree_merchant_id,
			'braintree_public_key' => $braintree_public_key,
			'braintree_private_key' => $braintree_private_key,
			'braintree_cse' => $braintree_cse,
			'twillo_account_sid' => $twillo_account_sid,
			'twillo_auth_token' => $twillo_auth_token,
			'twillo_number' => $twillo_number,
			'stripe_publishable_key' => $stripe_publishable_key,
			'stripe_secret_key' => $stripe_secret_key,
			'mail_driver' => $mail_driver,
			'email_address' => $email_address,
			'email_name' => $email_name,
			'mandrill_secret' => $mandrill_secret,
			'sendgrid_secret' => $sendgrid_secret,
			'default_business_model' => $settingBusinessModel->value,
			'provider_transfer_interval' => $settingsProviderTransferInterval,
			'provider_transfer_day' => $settingsProviderTransferDay,
			'payment_money' => $settingMoney->value,
			'payment_card' => $settingCard->value,
			'payment_voucher' => $settingVoucher->value,
			'default_payment' => $default_payment,
			'payments_by_user' => $settingPaymentsByUser,
			'customer_certy_url' => $customer_certy_url,
			'customer_certy_pass' => $customer_certy_pass,
			'customer_certy_type' => $customer_certy_type,
			'provider_certy_url' => $provider_certy_url,
			'provider_certy_pass' => $provider_certy_pass,
			'provider_certy_type' => $provider_certy_type,
			'gcm_browser_key' => $gcm_browser_key,
		);

		return Redirect::to('/admin/settings?success=1')
						->with('install', $install);
	}

	/**
	* saveImport
	*/
	public function saveImport() {
		// pega string e converte em array
		$array = array();
		eval ('$array = ' . Input::get('array') . ';'); // this will do what you want

		$category = Input::get('category') ;
		// eval no array

		// foreach
		foreach ($array as $key => $value){

			//return $key . "--" . $value;
			// verifico se existe
			$newSetting = Settings::findByKey($key);

			if(!$newSetting){ // se não existe eu crio
				$newSetting = new Settings();
				$newSetting->category = intval($category);
				$newSetting->sub_category = 0 ;
				$newSetting->page = 1 ;
				$newSetting->tool_tip = "" ;
			}
			$newSetting->key = $key ;
			$newSetting->value = $value ;

			$newSetting->save();
		}

		return Redirect::to("/admin/settings/import");
	}

	public function getAudioUrl() {
		return Settings::getAudioUrl();
	}

	public function integration() {
		return view('settings.integration')->with([
			'api_key' => Settings::getApiKey(),
			'integration_enabled' => Settings::getIntegrationEnabled()
		]);
	}

	public function generateNewApiKey() {
		Settings::generateNewApiKey();
		return redirect()->back();
	}

	public function generateNewInstitutionKey(Request $req, $id) {
		$institution = Institution::find($id);
		if($institution) {
			return Response::json([
				'success' => true,
				'api_key' => $institution->newApiKey()
			], 200);
		} else {
			return Response::json([
				'success' => false
			], 200);
		}
	}

	public function toggleIntegration() {
		Settings::setIntegrationEnabled(!Settings::getIntegrationEnabled());
		return redirect()->back();
	}
}







?>