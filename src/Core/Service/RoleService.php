<?php

namespace App\Core\Service;

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


    public function __construct(RoleHierarchyInterface $roleHierarchy)
    {
        $this->roleHierarchy = $roleHierarchy;
    }


    /**
     * Tests if the specified user has a specific role
     *
     * @param string $role The role value
     * @param UserInterface $user The user
     *
     * @return bool
     */
    public function isGranted(string $role, $user) : bool
    {
        foreach ($user->getRoles() as $userRole)
        {
            /** @var string[] $userRoles */
            $userRoles = $this->roleHierarchy->getReachableRoleNames([$userRole]);

            if (in_array($role, $userRoles))
            {
                return true;
            }
        }

        return false;
    }

}
