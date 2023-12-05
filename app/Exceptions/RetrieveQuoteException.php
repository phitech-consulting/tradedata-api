<?php

namespace App\Exceptions;

use Exception;

class RetrieveQuoteException extends Exception
{
    public function __construct($message = "Retrieve Quote Exception", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
