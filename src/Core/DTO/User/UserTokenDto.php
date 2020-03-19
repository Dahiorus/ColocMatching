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

    /**
     * @var \DateTime
     */
    private $expirationDate;


    public function __toString() : string
    {
        $expirationDate = empty($this->expirationDate) ? null : $this->expirationDate->format(DATE_ISO8601);

        return parent::__toString() . "[reason = " . $this->reason . ", username = " . $this->username
            . ", expirationDate=" . $expirationDate . "]";
    }


    /**
     * Indicates if the user token is expired
     *
     * @return bool
     * @throws \Exception
     */
    public function isExpired()
    {
        return $this->expirationDate < new \DateTime();
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


    public function getExpirationDate()
    {
        return $this->expirationDate;
    }


    public function setExpirationDate(\DateTime $expirationDate = null)
    {
        $this->expirationDate = $expirationDate;

        return $this;
    }


    public function getEntityClass() : string
    {
        return UserToken::class;
    }

}
