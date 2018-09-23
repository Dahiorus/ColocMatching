<?php

namespace App\Core\Exception\Persistence;

use Throwable;

class NonUniqueResultException extends PersistenceException
{

    public function __construct(int $code = 0, Throwable $previous = null)
    {
        parent::__construct("A non unique result was returned by the query", $code, $previous);
    }
}