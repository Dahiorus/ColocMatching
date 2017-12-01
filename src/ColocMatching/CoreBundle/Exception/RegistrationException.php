<?php

namespace ColocMatching\CoreBundle\Exception;

use ColocMatching\CoreBundle\Entity\User\User;
use Throwable;

class RegistrationException extends ColocMatchingException {

    public function __construct(User $user, Throwable $previous = null) {
        parent::__construct("Cannot register the user " . $user->getUsername(), 500, $previous);
    }
}