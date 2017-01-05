<?php

namespace ColocMatching\CoreBundle\Security\User;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

use ColocMatching\CoreBundle\Entity\User;
use ColocMatching\CoreBundle\Manager\User\UserManager;

/**
 * Description of UserProvider
 *
 * @author brondon.ung
 */
class UserProvider implements UserProviderInterface {
    /** @var UserManager */
    private $userManager;
    
    
    public function __construct(UserManager $userManager) {
        $this->userManager = $userManager;
    }
    
    
    public function loadUserByUsername($username): UserInterface {
        /** @var User */
        $user = $this->userManager->getByUsername($username);
        
        if (!$user) {
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist', $username));
        }
        
        return $user;
    }

    public function refreshUser(UserInterface $user): UserInterface {
        if (!$this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException(
                sprintf('Expected an instance of %s, but got "%s".',
                    $this->userManager->getClass(),
                    get_class($user)
            ));
        }
        
        $refreshUser = $this->userManager->getById($user->getId());
        
        if ($refreshUser === null) {
            throw new UsernameNotFoundException(
                sprintf('User with ID "%s" could not be reloaded.', $user->getId())
            );
        }
        
        return $refreshUser;
    }

    public function supportsClass($class): bool {
        return User::class === $class;
    }

}
