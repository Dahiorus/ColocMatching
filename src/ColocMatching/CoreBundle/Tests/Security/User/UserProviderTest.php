<?php

namespace ColocMatching\CoreBundle\Tests\Security\User;

use ColocMatching\CoreBundle\Tests\TestCase;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\User;

class UserProviderTest extends TestCase {

    private $userProvider;


    protected function setUp() {
        $this->userProvider = parent::getContainer()->get("coloc_matching.core.user_provider");
    }


    protected function tearDown() {
    }


    public function testLoadUserByUsername() {
        self::$logger->info("Test loading user by username");

        $username = "toto@test.fr";

        $user = $this->userProvider->loadUserByUsername($username);

        $this->assertNotNull($user);
        $this->assertEquals($username, $user->getUsername());
    }


    public function testLoadUserByUsernameWithNotFound() {
        self::$logger->info("Test loading user by username");

        $this->expectException(UsernameNotFoundException::class);

        $username = "bobo@test.fr";
        $this->userProvider->loadUserByUsername($username);
    }


    public function testRefreshUser() {
        self::$logger->info("Test refreshing user");

        $user = $this->userProvider->loadUserByUsername("toto@test.fr");
        $refreshUser = $this->userProvider->refreshUser($user);

        $this->assertEquals($user, $refreshUser);
    }


    public function testRefreshUserWithUnsupportedUser() {
        self::$logger->info("Test refreshing user with UnsupportedUserException");

        $this->expectException(UnsupportedUserException::class);

        $user = new User("toto", "toto");
        $this->userProvider->refreshUser($user);
    }

}