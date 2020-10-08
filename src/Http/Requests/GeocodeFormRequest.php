<?php

namespace Codificar\Geolocation\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

//Rules
use Codificar\Geolocation\Http\Rules\CheckUserToken;
use Codificar\Geolocation\Http\Rules\CheckUserId;

class GeocodeFormRequest extends FormRequest
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
            'token.required'    =>  trans('userController.token_missing'),
            'id.required'       =>  trans('userController.unique_id_missing'),
            'id.integer'        =>  trans('validation.integer', ['attribute' => trans('user.id')]),
            'token.string'      =>  trans('validation.string', ['attribute' => 'token']),
            'address.required'  =>  trans('maps_lib.address_required')
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
