<?php

namespace ColocMatching\CoreBundle\Security\User;

use Symfony\Component\Validator\Constraints as Assert;

class LostPassword
{
    /**
     * User token value
     * @var string
     *
     * @Assert\NotBlank
     */
    private $token;

    /**
     * User new plain password
     * @var string
     *
     * @Assert\NotBlank
     * @Assert\Length(min=8, max=4096)
     */
    private $newPassword;


    public function __toString()
    {
        return "LostPassword [token = " . $this->token . "]";
    }


    public function getToken()
    {
        return $this->token;
    }


    public function setToken(string $token)
    {
        $this->token = $token;

        return $this;
    }


    public function getNewPassword()
    {
        return $this->newPassword;
    }


    public function setNewPassword(?string $newPassword)
    {
        $this->newPassword = $newPassword;

        return $this;
    }

}
