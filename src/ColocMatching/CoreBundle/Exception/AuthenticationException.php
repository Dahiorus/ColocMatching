<?php

namespace ColocMatching\CoreBundle\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Exception thrown when an authentication error is detected
 *
 * @author Dahiorus
 */
class AuthenticationException extends HttpException {

    public function __construct($message = "Authentication error", \Exception $previous = null,
        array $headers = array (), $code = 0) {

        parent::__construct(Response::HTTP_FORBIDDEN, $message, $previous, $headers, $code);
    }
}