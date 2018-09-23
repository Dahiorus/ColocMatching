<?php

namespace App\Rest\Event;

use App\Core\DTO\User\UserDto;
use Symfony\Component\EventDispatcher\Event;

class RegistrationEvent extends Event
{
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
