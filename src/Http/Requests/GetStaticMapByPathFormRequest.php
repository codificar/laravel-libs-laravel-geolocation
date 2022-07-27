<?php

namespace Codificar\Geolocation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class GetStaticMapByPathFormRequest extends FormRequest
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

        return [
            'width' => ['required', 'integer'],
            'height' => ['required', 'integer'],
            'points' => ['array'],
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
            'width.required' => trans('geolocation.width_validation_required'),
            'width.integer' => trans('geolocation.width_validation_integer'),
            'height.required' => trans('geolocation.height_validation_required'),
            'height.integer' => trans('geolocation.height_validation_integer'),
            'path.array' => trans('geolgeolocation.path_validation_array'),

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