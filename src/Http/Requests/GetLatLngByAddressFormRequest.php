<?php

namespace Codificar\Geolocation\Http\Requests;

use Codificar\Geolocation\Http\Rules\CheckLat;
use Codificar\Geolocation\Http\Rules\CheckLong;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class GetLatLngByAddressFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $this->user = \User::whereId(request()->id)->whereToken(request()->token)->first();

        return [
            'address'       =>  ['required'],
            'latitude'      =>  ['required', new CheckLat($this->latitude)],
            'longitude'     =>  ['required', new CheckLong($this->longitude)],
            'clicker'       =>  [''],
            'place_id'      =>  [''],
            'lang'          =>  ['']
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'address.required'  =>  trans('geolocation.address_required'),
            'address.string'  =>  trans('geolocation.address_string')
        ];
    }

    /**
     * If validation has failed, return the error items
     *
     * @return Json
     */
    protected function failedValidation(Validator $validator)
    {
        // Pega as mensagens de erro
        $error_messages = $validator->errors()->all();

        // Exibe os parâmetros de erro
        throw new HttpResponseException(
            response()->json(
                [
                    'success' => false,
                    'errors' => $error_messages,
                    'error_code' => \ApiErrors::REQUEST_FAILED,
                    'error_messages' => $error_messages,
                ]
            ));
    }
}