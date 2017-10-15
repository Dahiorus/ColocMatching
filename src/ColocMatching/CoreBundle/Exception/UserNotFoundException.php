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
     */
    public function __construct(string $name, $value) {
        parent::__construct(User::class, $name, $value);
    }

}