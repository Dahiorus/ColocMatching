<?php

namespace ColocMatching\RestBundle\Event;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use Symfony\Component\EventDispatcher\Event;

class RegistrationEvent extends Event
{

    const REGISTERED_EVENT = "coloc_matching.user.registered";

    /**
     * @var UserDto
     */
    private $user;


    public function __construct(UserDto $user)
    {
        $this->user = $user;
    }


    /**
     * @return UserDto
     */
    public function getUser() : UserDto
    {
        return $this->user;
    }
}
