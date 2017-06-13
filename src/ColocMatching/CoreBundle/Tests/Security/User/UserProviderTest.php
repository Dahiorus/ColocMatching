<?php

namespace ColocMatching\CoreBundle\Tests\Security\User;

use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Exception\UserNotFoundException;
use ColocMatching\CoreBundle\Manager\User\UserManager;
use ColocMatching\CoreBundle\Security\User\UserProvider;
use ColocMatching\CoreBundle\Tests\TestCase;
use ColocMatching\CoreBundle\Tests\Utils\Mock\User\UserMock;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProviderTest extends TestCase {

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $userManager;

    /**
     * @var UserProviderInterface
     */
    private $userProvider;

    /**
     * @var LoggerInterface
     */
    private $logger;


    protected function setUp() {
        $this->userManager = $this->createMock(UserManager::class);
        $this->userProvider = new UserProvider($this->userManager);
        $this->logger = self::getContainer()->get("logger");
    }


    protected function tearDown() {
    }


    public function testLoadUserByUsername() {
        $this->logger->info("Test loading user by username");

        $username = "toto@test.fr";
        $expectedUser = UserMock::createUser(1, $username, "password", "User", "Test", UserConstants::TYPE_SEARCH);

        $this->userManager->expects($this->once())->method("findByUsername")->willReturn($expectedUser);

        $user = $this->userProvider->loadUserByUsername($username);

        $this->assertNotNull($user);
        $this->assertEquals($expectedUser, $user);
    }


    public function testLoadUserByUsernameWithNotFound() {
        $this->logger->info("Test loading a non existing user by username");

        $username = "bobo@test.fr";

        $this->userManager->expects($this->once())->method("findByUsername")->willThrowException(
            new UserNotFoundException("username", $username));
        $this->expectException(UsernameNotFoundException::class);

        $this->userProvider->loadUserByUsername($username);
    }


    public function testRefreshUser() {
        $this->logger->info("Test refreshing user");

        $expectedUser = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_SEARCH);

        $this->userManager->expects($this->once())->method("read")->with($expectedUser->getId())->willReturn(
            $expectedUser);

        $refreshUser = $this->userProvider->refreshUser($expectedUser);

        $this->assertEquals($expectedUser, $refreshUser);
    }


    public function testRefreshUserWithUnsupportedUser() {
        $this->logger->info("Test refreshing user with UnsupportedUserException");

        $this->expectException(UnsupportedUserException::class);

        $user = new User("toto", "toto");
        $this->userProvider->refreshUser($user);
    }

}