<?php

namespace Codificar\Geolocation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class GetStaticMapFormRequest extends FormRequest
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
            'scale' => ['integer'],
            'width' => ['required', 'integer'],
            'height' => ['required', 'integer'],
            'markers' => ['array'],
            'path' => ['array'],
            'center' => ['required_without_all:markers,path'],
            'zoom' => ['integer']
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
            'scale.integer' => trans('geolocation.scale_validation_integer'),
            'width.required' => trans('geolocation.width_validation_required'),
            'width.integer' => trans('geolocation.width_validation_integer'),
            'height.required' => trans('geolocation.height_validation_required'),
            'height.integer' => trans('geolocation.height_validation_integer'),
            'markers.array' => trans('geolgeolocation.markers_validation_array'),
            'path.array' => trans('geolgeolocation.path_validation_array'),
            'center.required_without_all' => trans('geolocation.center_validation_required_without'),
            'zoom.integer' => trans('geolocation.zoom_validation_integer'),
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