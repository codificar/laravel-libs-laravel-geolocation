<?php

namespace Codificar\Geolocation\Http\Rules;

use Illuminate\Contracts\Validation\Rule;

class CheckLat implements Rule {

    public $latitude;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($latitude) {
        $this->latitude = $latitude;
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
        if ($this->latitude != '' && $this->latitude != 0) {
            return true;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message() {

        return trans('geolocationTrans::geolocation.latitude_and_longitude_equals_zero');
    }

}