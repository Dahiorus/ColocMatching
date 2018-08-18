<?php

namespace App\Tests\Core\Service;

use App\Core\Entity\User\User;
use App\Core\Service\RoleService;
use App\Tests\Core\AbstractServiceTest;
use Symfony\Component\Security\Core\User\UserInterface;

class RoleServiceTest extends AbstractServiceTest
{
    /** @var RoleService */
    private $roleService;


    protected function setUp()
    {
        parent::setUp();

        $roleHierarchy = $this->getService("security.role_hierarchy");
        $userDtoMapper = $this->getService("coloc_matching.core.user_dto_mapper");
        $this->roleService = new RoleService($roleHierarchy, $userDtoMapper);
    }


    /**
     * Creates a user with roles
     *
     * @param array $roles The user roles
     *
     * @return UserInterface
     */
    private function createUser(array $roles = array ()) : UserInterface
    {
        $user = new User("user@test.fr", "password", "User", "Test");

        foreach ($roles as $role)
        {
            $user->addRole($role);
        }

        return $user;
    }


    /**
     * @test
     */
    public function userRoleIsGranted()
    {
        $user = $this->createUser(array ("ROLE_SUPER_ADMIN"));

        $result = $this->roleService->isGranted("ROLE_ADMIN", $user);

        self::assertTrue($result, "Expected user is admin");
    }


    /**
     * @test
     */
    public function userRoleIsNotGranted()
    {
        $user = $this->createUser();

        $result = $this->roleService->isGranted("ROLE_ADMIN", $user);

        self::assertFalse($result, "Expected user role not granted");
    }

}
