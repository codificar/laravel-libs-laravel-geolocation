<?php

namespace Codificar\Geolocation\Http\Rules;

use Illuminate\Contracts\Validation\Rule;

class CheckWayPoints implements Rule {

    public $wayPoints;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($wayPoints) {
        $this->wayPoints = $wayPoints;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value) {
        if (is_string($this->wayPoints) && is_array(json_decode($this->wayPoints, true))) {

            $ways = json_decode($this->wayPoints, true);

            if(count($ways) < 2)
                return false;

            foreach ($ways as $way) {
                if (
                    (!$way[0] || !$way[1]) || 
                    ($way[0] == '' || $way[1] == '') && 
                    ($way[0] == 0 || $way[1] == 0)
                ) {
                    return false;
                }
            }

            return true;
        }else{
            return false;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message() {

        return trans('googleMapsController.incorrect_object_format');
    }

}
