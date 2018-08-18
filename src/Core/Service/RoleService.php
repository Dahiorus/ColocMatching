<?php

namespace App\Core\Service;

use App\Core\DTO\User\UserDto;
use App\Core\Mapper\User\UserDtoMapper;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Service to test user's roles
 *
 * @author Dahiorus
 */
class RoleService
{
    /** @var RoleHierarchyInterface */
    private $roleHierarchy;

    /** @var UserDtoMapper */
    private $userDtoMapper;


    public function __construct(RoleHierarchyInterface $roleHierarchy, UserDtoMapper $userDtoMapper)
    {
        $this->roleHierarchy = $roleHierarchy;
        $this->userDtoMapper = $userDtoMapper;
    }


    /**
     * Tests if the specified user has a specific role
     *
     * @param string $role The role value
     * @param UserDto|UserInterface $user The user
     *
     * @return bool
     */
    public function isGranted(string $role, $user) : bool
    {
        $entity = ($user instanceof UserDto) ? $this->userDtoMapper->toEntity($user) : $user;
        $r = new Role($role);

        foreach ($entity->getRoles() as $userRole)
        {
            /** @var Role[] $userRoles */
            $userRoles = $this->roleHierarchy->getReachableRoles(array (new Role($userRole)));

            if (in_array($r, $userRoles))
            {
                return true;
            }
        }

        return false;
    }

}
