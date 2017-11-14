<?php

namespace ColocMatching\CoreBundle\Exception\Persistence;

use Throwable;

class NoResultException extends PersistenceException {

    public function __construct(int $code = 0, Throwable $previous = null) {
        parent::__construct("No result returned by the query", $code, $previous);
    }
}