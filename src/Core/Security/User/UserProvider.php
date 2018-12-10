<?php

namespace App\Core\Security\User;

use App\Core\DTO\User\UserDto;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Manager\User\UserDtoManagerInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * User provider for the authentication system
 *
 * @author Dahiorus
 */
class UserProvider implements UserProviderInterface
{
    /** @var UserDtoManagerInterface */
    private $userManager;


    public function __construct(UserDtoManagerInterface $userManager)
    {
        $this->userManager = $userManager;
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
                sprintf("Expected an instance of %s, but got '%s'", UserDto::class, get_class($user)));
        }

        return $this->getUser($user->getUsername());
    }


    /**
     * @inheritdoc
     */
    public function supportsClass($class) : bool
    {
        return UserDto::class === $class;
    }


    /**
     * Finds a user by its username
     *
     * @param string $username
     *
     * @return UserDto
     * @throws UsernameNotFoundException
     */
    private function getUser(string $username) : UserDto
    {
        try
        {
            return $this->userManager->findByUsername($username);
        }
        catch (EntityNotFoundException $e)
        {
            throw new UsernameNotFoundException("No user found with username '$username'");
        }
    }
}
