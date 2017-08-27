<?php

namespace ColocMatching\CoreBundle\Security\User;

use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\UserNotFoundException;
use ColocMatching\CoreBundle\Manager\User\UserManagerInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * User provider for the authentication system
 *
 * @author brondon.ung
 */
class UserProvider implements UserProviderInterface {

    /**
     * @var UserManagerInterface
     */
    private $userManager;


    public function __construct(UserManagerInterface $userManager) {
        $this->userManager = $userManager;
    }


    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Security\Core\User\UserProviderInterface::loadUserByUsername()
     */
    public function loadUserByUsername($username) : UserInterface {
        try {
            return $this->userManager->findByUsername($username);
        }
        catch (UserNotFoundException $e) {
            throw new UsernameNotFoundException($e->getMessage());
        }
    }


    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Security\Core\User\UserProviderInterface::refreshUser()
     */
    public function refreshUser(UserInterface $user) : UserInterface {
        if (!$this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException(
                sprintf("Expected an instance of %s, but got '%s'", User::class, get_class($user)));
        }

        try {
            return $this->userManager->findByUsername($user->getUsername());
        }
        catch (UserNotFoundException $e) {
            throw new UsernameNotFoundException(sprintf("The User with username '%s' could not be refresh",
                $user->getUsername()));
        }
    }


    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Security\Core\User\UserProviderInterface::supportsClass()
     */
    public function supportsClass($class) : bool {
        return User::class === $class;
    }

}
