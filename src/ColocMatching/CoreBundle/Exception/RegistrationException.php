<?php

namespace ColocMatching\CoreBundle\Exception;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use Throwable;

/**
 * Exception thrown on a user registration error
 *
 * @author Dahiorus
 */
class RegistrationException extends ColocMatchingException
{
    public function __construct(UserDto $user, Throwable $previous = null)
    {
        parent::__construct("Cannot register the user " . $user->getUsername(), 500, $previous);
    }
}