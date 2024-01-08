<?php

namespace App\Exceptions;

use Exception;

class QuoteStoreException extends Exception
{
    public function __construct($message = "QuoteStoreException", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
