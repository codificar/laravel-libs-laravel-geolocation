<?php

namespace Codificar\Geolocation\Exceptions;

use Exception;

class InvalidKeyException extends Exception
{
    protected $message = 'Invalid or Expired Key.';

    public function render(){
        return response()->json([
            'error' => class_basename($this),
            'success' => false,
            'message' => $this->getMessage(),
        ], 403);
    }
}
