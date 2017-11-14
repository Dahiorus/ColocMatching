<?php

namespace ColocMatching\CoreBundle\Exception\Persistence;

use Throwable;

class TransactionRequiredException extends PersistenceException {

    public function __construct(int $code = 0, Throwable $previous = null) {
        parent::__construct("An open transaction is required for this operation", $code, $previous);
    }
}