<?php

namespace ColocMatching\CoreBundle\Exception;

use ColocMatching\CoreBundle\Entity\Invitation\Invitation;

class InvitationNotFoundException extends EntityNotFoundException {

    /**
     * Constructor
     *
     * @param string $name The name of the attribute on which the exception would be throw
     * @param mixed $value The value of the attribute
     */
    public function __construct(string $name, $value) {
        parent::__construct(Invitation::class, $name, $value);
    }
}