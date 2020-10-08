<?php

namespace Codificar\Geolocation\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

//Rules
use Illuminate\Http\Exceptions\HttpResponseException;
use Codificar\Geolocation\Http\Rules\CheckUserToken;
use Codificar\Geolocation\Http\Rules\CheckUserId;
use Codificar\Geolocation\Http\Rules\CheckLat;
use Codificar\Geolocation\Http\Rules\CheckLong;

class PlacesFormRequest extends FormRequest
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
            'place'     =>  ['required', 'string'],
            'latitude'  =>  ['required', new CheckLat($this->latitude)],
            'longitude' =>  ['required', new CheckLong($this->longitude)]
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
            'token.required'        =>  trans('userController.token_missing'),
            'id.required'           =>  trans('userController.unique_id_missing'),
            'place.required'        =>  trans('maps_lib.place_is_required'),
        ];
    }

    /**
     * Caso a validação falhe, retorna os itens de erro
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
                'error' => $error_messages[0],
                'error_code' => \ApiErrors::REQUEST_FAILED,
                'error_messages' => $error_messages,
            ]
        ));
    }
}