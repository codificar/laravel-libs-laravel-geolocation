<?php

namespace Codificar\Geolocation\Http\Rules;

use Illuminate\Contracts\Validation\Rule;

class CheckUserId implements Rule {

    public $user;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($user) {
        $this->user = $user;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value) {

        if ($this->user) {
            return true;
        }
    }
    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message() {

        return trans('geolocationTrans::geolocation.id_required');
    }

}