<?php

namespace Codificar\Geolocation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Codificar\Geolocation\Http\Rules\CheckWayPoints;
class RouteWayPointsRequest extends FormRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize() {
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules() {
		$this->user = \User::whereId(request()->id)->whereToken(request()->token)->first();
        
        return [			
			'waypoints' =>  ['required', new CheckWayPoints(request()->waypoints)]
		];
	}

	public function messages() {
		return [            
			'waypoints.required'=> trans('geolocationTrans::geolocation.waypoints_required')
		];
	}

	/**
	 * Retorna um json caso a validação falhe.
	 * 
	 * @throws HttpResponseException
	 * @return json
	 */
	protected function failedValidation(Validator $validator) {
		throw new HttpResponseException(
			response()->json([
				'success' => false,
				'errors' => $validator->errors()->all(),
				'error_code' => \ApiErrors::REQUEST_FAILED
			])
		);
	}
}
