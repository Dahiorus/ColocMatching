<?php

namespace App\Tests\Core\Security\User;

use App\Core\DTO\User\UserDto;
use App\Core\Entity\User\User;
use App\Core\Entity\User\UserType;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Core\Security\User\UserProvider;
use App\Tests\AbstractServiceTest;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProviderTest extends AbstractServiceTest
{
    /**
     * @var MockObject
     */
    private $userManager;

    /**
     * @var UserProviderInterface
     */
    private $userProvider;


    protected function setUp()
    {
        parent::setUp();

        $this->userManager = $this->createMock(UserDtoManagerInterface::class);
        $this->userProvider = new UserProvider($this->userManager);
    }


    private function createUser(int $id, string $email, string $password, string $firstName,
        string $lastName) : UserDto
    {
        $user = new UserDto();

        $user->setId($id)
            ->setEmail($email)
            ->setPlainPassword($password)
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setType(UserType::SEARCH);

        return $user;
    }


    /**
     * @test
     */
    public function loadUserByUsername()
    {
        $username = "toto@test.fr";
        $expectedUser = $this->createUser(1, $username, "password", "User", "Test");

        $this->userManager->expects($this->once())
            ->method("findByUsername")
            ->with($username)
            ->willReturn($expectedUser);

        $user = $this->userProvider->loadUserByUsername($username);

        $this->assertNotNull($user);
        $this->assertEquals($expectedUser->getUsername(), $user->getUsername());
    }


    /**
     * @test
     */
    public function loadUserByUsernameWithNotFound()
    {
        $username = "bobo@test.fr";
        $this->userManager->expects($this->once())
            ->method("findByUsername")
            ->with($username)
            ->willThrowException(new EntityNotFoundException(User::class, "username", $username));

        $this->expectException(UsernameNotFoundException::class);

        $this->userProvider->loadUserByUsername($username);
    }


    /**
     * @test
     */
    public function refreshUser()
    {
        $expectedUser = $this->createUser(1, "user@test.fr", "password", "User", "Test");

        $this->userManager->expects($this->once())
            ->method("findByUsername")
            ->with($expectedUser->getUsername())
            ->willReturn($expectedUser);

        $refreshUser = $this->userProvider->refreshUser($expectedUser);

        $this->assertEquals($expectedUser->getUsername(), $refreshUser->getUsername());
    }


    /**
     * @test
     * @expectedException \Symfony\Component\Security\Core\Exception\UnsupportedUserException
     */
    public function refreshUserWithUnsupportedUser()
    {
        $user = new \Symfony\Component\Security\Core\User\User("toto", "toto");
        $this->userProvider->refreshUser($user);
    }

}
