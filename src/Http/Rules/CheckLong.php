<?php

namespace Codificar\Geolocation\Http\Rules;

use Illuminate\Contracts\Validation\Rule;

class CheckLong implements Rule {

    public $longitude;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($longitude) {
        $this->longitude = $longitude;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value) {

        //verifica destino
        if ($this->longitude != '' && $this->longitude != 0) {
            return true;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message() {

        return trans('providerController.latitude_and_longitude_equals_zero');
    }

}