<?php

namespace App\Core\Exception\Persistence;

use Throwable;

class UnexpectedResultException extends PersistenceException
{

    public function __construct(int $code = 0, Throwable $previous = null)
    {
        parent::__construct("An unexpected result was returned by the query", $code, $previous);
    }
}