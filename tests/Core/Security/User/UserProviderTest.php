<?php

namespace App\Tests\Core\Security\User;

use App\Core\Entity\User\User;
use App\Core\Entity\User\UserConstants;
use App\Core\Repository\User\UserRepository;
use App\Core\Security\User\UserProvider;
use App\Tests\Core\AbstractServiceTest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProviderTest extends AbstractServiceTest
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $userRepository;

    /**
     * @var UserProviderInterface
     */
    private $userProvider;


    protected function setUp()
    {
        parent::setUp();

        $this->userRepository = $this->createMock(UserRepository::class);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method("getRepository")->with(User::class)->willReturn($this->userRepository);

        $this->userProvider = new UserProvider($entityManager);
    }


    private function createUser(int $id, string $email, string $password, string $firstName,
        string $lastName) : User
    {
        $user = new User($email, $password, $firstName, $lastName);

        $user->setId($id);
        $user->setType(UserConstants::TYPE_SEARCH);

        return $user;
    }


    /**
     * @test
     */
    public function loadUserByUsername()
    {
        $username = "toto@test.fr";
        $expectedUser = $this->createUser(1, $username, "password", "User", "Test");

        $this->userRepository->expects($this->once())
            ->method("findOneBy")
            ->with(array ("email" => $expectedUser->getUsername()))
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
        $this->userRepository->expects($this->once())
            ->method("findOneBy")
            ->with(array ("email" => $username))
            ->willReturn(null);

        $this->expectException(UsernameNotFoundException::class);

        $this->userProvider->loadUserByUsername($username);
    }


    /**
     * @test
     */
    public function refreshUser()
    {
        $expectedUser = $this->createUser(1, "user@test.fr", "password", "User", "Test");

        $this->userRepository->expects($this->once())
            ->method("findOneBy")
            ->with(array ("email" => $expectedUser->getUsername()))
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