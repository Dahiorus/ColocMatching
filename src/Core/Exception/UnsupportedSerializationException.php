<?php

namespace App\Core\Exception;

class UnsupportedSerializationException extends ColocMatchingException
{
    public function __construct($value, \Throwable $previous = null)
    {
        parent::__construct("Cannot transform [$value] into an object", 400, $previous);
    }

}
