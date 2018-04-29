<?php

namespace ColocMatching\CoreBundle\Security\User;

use ColocMatching\CoreBundle\DAO\UserDao;
use ColocMatching\CoreBundle\Entity\User\User;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * User provider for the authentication system
 *
 * @author brondon.ung
 */
class UserProvider implements UserProviderInterface
{
    /** @var UserDao */
    private $userDao;


    public function __construct(UserDao $userDao)
    {
        $this->userDao = $userDao;
    }


    /**
     * @inheritdoc
     */
    public function loadUserByUsername($username) : UserInterface
    {
        return $this->getUser($username);
    }


    /**
     * @inheritdoc
     */
    public function refreshUser(UserInterface $user) : UserInterface
    {
        if (!$this->supportsClass(get_class($user)))
        {
            throw new UnsupportedUserException(
                sprintf("Expected an instance of %s, but got '%s'", User::class, get_class($user)));
        }

        return $this->getUser($user->getUsername());
    }


    /**
     * @inheritdoc
     */
    public function supportsClass($class) : bool
    {
        return User::class === $class;
    }


    /**
     * Finds a user by its username
     *
     * @param string $username
     *
     * @return User
     * @throws UsernameNotFoundException
     */
    private function getUser(string $username) : User
    {
        /** @var User $user */
        $user = $this->userDao->findOne(array ("email" => $username));

        if (empty($user))
        {
            throw new UsernameNotFoundException("No user found with username '$username'");
        }

        return $user;
    }
}
