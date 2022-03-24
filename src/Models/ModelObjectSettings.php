<?php
namespace Codificar\Geolocation\Models;

use stdClass;
use ApplicationSettingsViewModel;

class ModelObjectSettings extends stdClass {

    // constructor
    public function __construct() {  }

    public function __get($attribute){
        try {
            return $this->$attribute ;
        }
        catch (Exception $ex) {
            return new ApplicationSettingsViewModel() ;
        }
    }
}
