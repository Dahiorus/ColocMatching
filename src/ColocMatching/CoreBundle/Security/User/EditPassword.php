<?php

namespace ColocMatching\CoreBundle\Security\User;

use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Validator\Constraint\UserPassword;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Used to edit the user password
 *
 * @UserPassword
 * @SWG\Definition(definition="EditPassword")
 *
 * @author Dahiorus
 */
class EditPassword
{

    /**
     * @var User
     */
    private $user;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Length(min=8, max=4096)
     * @SWG\Property(description="The old password of the user", minLength=8, maxLength=4096)
     */
    private $oldPassword;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Length(min=8, max=4096)
     * @SWG\Property(description="The new password of the user", minLength=8, maxLength=4096)
     */
    private $newPassword;


    /**
     * EditPassword constructor.
     *
     * @param User $user The user to edit password
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }


    /**
     * @return User
     */
    public function getUser() : User
    {
        return $this->user;
    }


    /**
     * @return string
     */
    public function getOldPassword()
    {
        return $this->oldPassword;
    }


    /**
     * @param string $oldPassword
     *
     * @return $this
     */
    public function setOldPassword(string $oldPassword)
    {
        $this->oldPassword = $oldPassword;

        return $this;
    }


    /**
     * @return string
     */
    public function getNewPassword()
    {
        return $this->newPassword;
    }


    /**
     * @param string $newPassword
     *
     * @return $this
     */
    public function setNewPassword(string $newPassword)
    {
        $this->newPassword = $newPassword;

        return $this;
    }

}