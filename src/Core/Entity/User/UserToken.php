<?php

namespace App\Core\Entity\User;

use App\Core\Entity\AbstractEntity;
use App\Core\Repository\User\UserTokenRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(readOnly=true, repositoryClass=UserTokenRepository::class)
 * @ORM\Table(name="user_token", uniqueConstraints={
 *   @ORM\UniqueConstraint(name="UK_USER_REASON", columns={ "username", "reason" })
 * }, indexes={
 *     @ORM\Index(name="IDX_USER_TOKEN_VALUE", columns={ "token" }),
 *     @ORM\Index(name="IDX_USER_TOKEN_REASON", columns={ "reason" }),
 *     @ORM\Index(name="IDX_USER_TOKEN_USERNAME", columns={ "username" })
 * })
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="user_tokens")
 *
 * @author Dahiorus
 */
class UserToken extends AbstractEntity
{
    const REGISTRATION_CONFIRMATION = "registration_confirmation";
    const LOST_PASSWORD = "lost_password";

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string")
     */
    private $token;

    /**
     * @var string
     *
     * @ORM\Column(name="reason", type="string")
     */
    private $reason;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string")
     */
    private $username;

    /**
     * @var \DateTimeImmutable
     *
     * @ORM\Column(name="expiration_date", type="datetime_immutable")
     */
    private $expirationDate;


    public function __construct(string $token, string $username, string $reason, \DateTimeImmutable $expirationDate)
    {
        $this->token = $token;
        $this->username = $username;
        $this->reason = $reason;
        $this->expirationDate = $expirationDate;
    }


    public function __toString() : string
    {
        return parent::__toString() . "[reason = " . $this->reason . ", username = " . $this->username
            . ", expirationDate=" . $this->expirationDate->format(DATE_ISO8601) . "]";
    }


    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }


    /**
     * @param string $token
     *
     * @return UserToken
     */
    public function setToken(?string $token)
    {
        $this->token = $token;

        return $this;
    }


    /**
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }


    /**
     * @param string $reason
     *
     * @return UserToken
     */
    public function setReason(?string $reason)
    {
        $this->reason = $reason;

        return $this;
    }


    /**
     * @return string
     */
    public function getUsername() : string
    {
        return $this->username;
    }


    /**
     * @param string $username
     *
     * @return UserToken
     */
    public function setUsername(?string $username)
    {
        $this->username = $username;

        return $this;
    }


    /**
     * @return \DateTimeImmutable
     */
    public function getExpirationDate() : \DateTimeImmutable
    {
        return $this->expirationDate;
    }


    /**
     * @param \DateTimeImmutable $expirationDate
     *
     * @return UserToken
     */
    public function setExpirationDate(\DateTimeImmutable $expirationDate = null)
    {
        $this->expirationDate = $expirationDate;

        return $this;
    }

}
