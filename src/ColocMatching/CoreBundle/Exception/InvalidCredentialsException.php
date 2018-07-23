<?php

namespace ColocMatching\CoreBundle\Exception;

use Throwable;

class InvalidCredentialsException extends ColocMatchingException
{
    /**
     * InvalidCredentialsException constructor.
     *
     * @param string $message [optional] The exception message
     * @param Throwable $previous [optional] The cause
     */
    public function __construct(string $message = "Invalid credentials", Throwable $previous = null)
    {
        parent::__construct($message, 401, $previous);
    }
}