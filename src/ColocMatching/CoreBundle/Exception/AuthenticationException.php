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

    /**
     * AuthenticationException constructor.
     *
     * @param string $message
     * @param \Exception|null $previous
     * @param array $headers
     */
    public function __construct($message = "Authentication error", \Exception $previous = null,
        array $headers = array ()) {

        parent::__construct(Response::HTTP_UNAUTHORIZED, $message, $previous, $headers, 401);
    }
}