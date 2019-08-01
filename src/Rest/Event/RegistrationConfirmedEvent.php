<?php

namespace App\Rest\Event;

use App\Core\DTO\User\UserDto;
use Symfony\Contracts\EventDispatcher\Event;

class RegistrationConfirmedEvent extends Event
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
