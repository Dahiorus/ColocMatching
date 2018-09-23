<?php

namespace App\Core\DTO\User;

use App\Core\DTO\AbstractDto;
use App\Core\Entity\User\UserToken;

/**
 * @author Dahiorus
 */
class UserTokenDto extends AbstractDto
{
    /**
     * @var string
     */
    private $token;

    /**
     * @var string
     */
    private $reason;

    /**
     * @var string
     */
    private $username;


    public function __toString() : string
    {
        return parent::__toString() . "[reason = " . $this->reason . ", username = " . $this->username . "]";
    }


    public function getToken()
    {
        return $this->token;
    }


    public function setToken(?string $token)
    {
        $this->token = $token;

        return $this;
    }


    public function getReason()
    {
        return $this->reason;
    }


    public function setReason(?string $reason)
    {
        $this->reason = $reason;

        return $this;
    }


    public function getUsername() : string
    {
        return $this->username;
    }


    public function setUsername(?string $username)
    {
        $this->username = $username;

        return $this;
    }


    public function getEntityClass() : string
    {
        return UserToken::class;
    }

}