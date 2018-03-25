<?php

namespace ColocMatching\CoreBundle\Security\User;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
use ColocMatching\CoreBundle\Mapper\User\UserDtoMapper;
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
    /**
     * @var UserDtoManagerInterface
     */
    private $userManager;

    /**
     * @var UserDtoMapper
     */
    private $userDtoMapper;


    public function __construct(UserDtoManagerInterface $userManager, UserDtoMapper $userDtoMapper)
    {
        $this->userManager = $userManager;
        $this->userDtoMapper = $userDtoMapper;
    }


    /**
     * @inheritdoc
     */
    public function loadUserByUsername($username) : UserInterface
    {
        try
        {
            /** @var UserDto $user */
            $user = $this->userManager->findByUsername($username);

            return $this->userDtoMapper->toEntity($user);
        }
        catch (EntityNotFoundException $e)
        {
            throw new UsernameNotFoundException($e->getMessage());
        }
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

        try
        {
            /** @var UserDto $refreshedUser */
            $refreshedUser = $this->userManager->findByUsername($user->getUsername());

            return $this->userDtoMapper->toEntity($refreshedUser);
        }
        catch (EntityNotFoundException $e)
        {
            throw new UsernameNotFoundException(sprintf("The User with username '%s' could not be refresh",
                $user->getUsername()));
        }
    }


    /**
     * @inheritdoc
     */
    public function supportsClass($class) : bool
    {
        return User::class === $class;
    }

}
