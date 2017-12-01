<?php

namespace ColocMatching\CoreBundle\Event;

use ColocMatching\CoreBundle\Entity\User\User;
use Symfony\Component\EventDispatcher\Event;

class RegistrationEvent extends Event {

    const REGISTERED_EVENT = "coloc_matching.user.registered";

    /**
     * @var User
     */
    private $user;


    public function __construct(User $user) {
        $this->user = $user;
    }


    /**
     * @return User
     */
    public function getUser() : User {
        return $this->user;
    }
}
