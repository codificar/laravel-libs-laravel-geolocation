<?php

namespace App\Http\Requests\api\v3;

use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

use Codificar\Geolocation\Http\Rules\CheckUserToken;
use Codificar\Geolocation\Http\Rules\CheckUserId;
use Codificar\Geolocation\Http\Rules\CheckLat;
use Codificar\Geolocation\Http\Rules\CheckLong;

class GeocodeReverseFormRequest extends FormRequest
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
            'latitude'      =>  ['required', new CheckLat($this->latitude)],
            'longitude'     =>  ['required', new CheckLong($this->longitude)],
            'clicker'       =>  ['']
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
            'token.string'      =>  trans('validation.string', ['attribute' => 'token'])
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
