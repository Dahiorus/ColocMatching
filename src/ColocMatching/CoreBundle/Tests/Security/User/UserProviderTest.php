<?php

namespace ColocMatching\CoreBundle\Tests\Security\User;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
use ColocMatching\CoreBundle\Mapper\User\UserDtoMapper;
use ColocMatching\CoreBundle\Security\User\UserProvider;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProviderTest extends KernelTestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $userManager;

    /**
     * @var UserDtoMapper
     */
    private $userDtoMapper;

    /**
     * @var UserProviderInterface
     */
    private $userProvider;

    /**
     * @var LoggerInterface
     */
    private $logger;


    public static function setUpBeforeClass()
    {
        self::bootKernel();
    }


    public static function tearDownAfterClass()
    {
        self::ensureKernelShutdown();
    }


    protected function setUp()
    {
        $this->userManager = $this->createMock(UserDtoManagerInterface::class);
        $this->userDtoMapper = static::$kernel->getContainer()->get("coloc_matching.core.user_dto_mapper");
        $this->userProvider = new UserProvider($this->userManager, $this->userDtoMapper);
        $this->logger = static::$kernel->getContainer()->get("logger");
    }


    protected function tearDown()
    {
    }


    private function createUserDto(int $id, string $email, string $password, string $firstName,
        string $lastName) : UserDto
    {
        $user = new UserDto();

        $user->setId($id);
        $user->setEmail($email);
        $user->setPlainPassword($password);
        $user->setFirstName($firstName);
        $user->setLastName($lastName);

        return $user;
    }


    /**
     * @test
     */
    public function loadUserByUsername()
    {
        $this->logger->info("Test loading user by username");

        $username = "toto@test.fr";
        $expectedUser = $this->createUserDto(1, $username, "password", "User", "Test");

        $this->userManager->expects($this->once())->method("findByUsername")->willReturn($expectedUser);

        $user = $this->userProvider->loadUserByUsername($username);

        $this->assertNotNull($user);
        $this->assertEquals($expectedUser->getUsername(), $user->getUsername());
    }


    /**
     * @test
     */
    public function loadUserByUsernameWithNotFound()
    {
        $this->logger->info("Test loading a non existing user by username");

        $username = "bobo@test.fr";

        $this->userManager->expects($this->once())->method("findByUsername")->willThrowException(
            new EntityNotFoundException(User::class, "username", $username));
        $this->expectException(UsernameNotFoundException::class);

        $this->userProvider->loadUserByUsername($username);
    }


    /**
     * @test
     */
    public function refreshUser()
    {
        $this->logger->info("Test refreshing user");

        $expectedUser = new User("user@test.fr", "password", "User", "Test");
        $expectedUser->setId(1);

        $this->userManager->expects($this->once())->method("findByUsername")->with($expectedUser->getUsername())
            ->willReturn($this->userDtoMapper->toDto($expectedUser));

        $refreshUser = $this->userProvider->refreshUser($expectedUser);

        $this->assertEquals($expectedUser->getUsername(), $refreshUser->getUsername());
    }


    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\UnsupportedUserException
     */
    public function testRefreshUserWithUnsupportedUser()
    {
        $this->logger->info("Test refreshing user with UnsupportedUserException");

        $user = new \Symfony\Component\Security\Core\User\User("toto", "toto");
        $this->userProvider->refreshUser($user);
    }

}