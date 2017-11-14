<?php

namespace ColocMatching\CoreBundle\Exception\Persistence;

use ColocMatching\CoreBundle\Exception\ColocMatchingException;
use Throwable;

/**
 * Exception used to map an ORM exception
 */
class PersistenceException extends ColocMatchingException {

    public function __construct(string $message = "Unexpected persistence exception", int $code = 0,
        Throwable $previous = null) {

        parent::__construct($message, $code, $previous);
    }

}