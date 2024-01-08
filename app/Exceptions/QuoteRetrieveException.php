<?php

namespace App\Exceptions;

use Exception;

class QuoteRetrieveException extends Exception
{
    public function __construct($message = "QuoteRetrieveException", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
