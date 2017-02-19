<?php

namespace ColocMatching\CoreBundle\Tests\Manager\User;

use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Exception\UserNotFoundException;
use ColocMatching\CoreBundle\Manager\User\UserManager;
use ColocMatching\CoreBundle\Repository\Filter\UserFilter;
use ColocMatching\CoreBundle\Tests\TestCase;

/**
 * Unit tests for UserManagers
 *
 * @author brondon.ung
 */
class UserManagerTest extends TestCase {

    private $userManager;


    protected function setUp() {
        $this->userManager = self::getContainer()->get("coloc_matching.core.user_manager");
    }


    protected function tearDown() {
    }


    public function testCreateUser() {
        self::$logger->info("Test creating a User");

        $data = array ("email" => "user@phpunit.fr", "plainPassword" => "password", "firstname" => "User",
            "lastname" => "Test");
        $user = $this->userManager->create($data);

        $this->assertNotNull($user);
        $this->assertEquals("user@phpunit.fr", $user->getEmail());
        $this->assertEquals("User", $user->getFirstname());
        $this->assertEquals("Test", $user->getLastname());
    }


    public function testCreateUserWithFailure() {
        self::$logger->info("Test creating a User");

        $this->expectException(InvalidFormDataException::class);

        $data = array ("email" => "user-fail@phpunit.fr");
        $this->userManager->create($data);
    }


    public function testListUsers() {
        self::$logger->info("Test listing users");

        $users = $this->userManager->list(new UserFilter());

        $this->assertNotNull($users);

        foreach ($users as $user) {
            $this->assertInstanceOf(User::class, $user);
        }
    }


    public function testReadUser() {
        self::$logger->info("Test reading user");

        $user = $this->userManager->read(1);

        $this->assertNotNull($user);
        $this->assertEquals(1, $user->getId());
    }


    public function testReadUserWithFailure() {
        self::$logger->info("Test reading user with failure");

        $this->expectException(UserNotFoundException::class);

        $this->userManager->read(999);
    }

}
