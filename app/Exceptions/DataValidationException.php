<?php

namespace App\Exceptions;

use Exception;

class DataValidationException extends Exception
{
    public function __construct($message = "Validation Error", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
