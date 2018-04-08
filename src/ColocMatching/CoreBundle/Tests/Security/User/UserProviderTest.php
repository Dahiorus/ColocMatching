<?php

namespace ColocMatching\CoreBundle\Tests\Security\User;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
use ColocMatching\CoreBundle\Mapper\User\UserDtoMapper;
use ColocMatching\CoreBundle\Security\User\UserProvider;
use ColocMatching\CoreBundle\Tests\AbstractServiceTest;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProviderTest extends AbstractServiceTest
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


    protected function setUp()
    {
        parent::setUp();

        $this->userManager = $this->createMock(UserDtoManagerInterface::class);
        $this->userDtoMapper = $this->getService("coloc_matching.core.user_dto_mapper");
        $this->userProvider = new UserProvider($this->userManager, $this->userDtoMapper);
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
     * @test
     * @expectedException \Symfony\Component\Security\Core\Exception\UnsupportedUserException
     */
    public function refreshUserWithUnsupportedUser()
    {
        $this->logger->info("Test refreshing user with UnsupportedUserException");

        $user = new \Symfony\Component\Security\Core\User\User("toto", "toto");
        $this->userProvider->refreshUser($user);
    }

}