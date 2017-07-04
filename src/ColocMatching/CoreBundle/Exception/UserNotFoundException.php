<?php

namespace ColocMatching\CoreBundle\Exception;

use ColocMatching\CoreBundle\Entity\User\User;

/**
 * Exception thrown when no user is found by the specified attribute name
 *
 * @author Dahiorus
 */
final class UserNotFoundException extends EntityNotFoundException {


    /**
     * Constructor
     *
     * @param string $name The name of the attribute on which the exception would be throw
     * @param mixed $value The value of the attribute
     * @param \Exception $previous
     * @param int $code
     */
    public function __construct(string $name, $value, \Exception $previous = null, int $code = 0) {
        parent::__construct(User::class, $name, $value, $previous, $code);
    }

}