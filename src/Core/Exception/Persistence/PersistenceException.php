<?php

namespace App\Core\Exception\Persistence;

use App\Core\Exception\ColocMatchingException;
use Throwable;

/**
 * Exception used to map an ORM exception
 */
class PersistenceException extends ColocMatchingException
{

    public function __construct(string $message, int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}