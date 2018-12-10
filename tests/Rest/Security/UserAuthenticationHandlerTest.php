<?php

namespace App\Tests\Rest\Security;

use App\Core\Entity\User\User;
use App\Core\Entity\User\UserStatus;
use App\Core\Exception\InvalidCredentialsException;
use App\Core\Exception\InvalidFormException;
use App\Core\Repository\User\UserRepository;
use App\Rest\Security\UserAuthenticationHandler;
use App\Tests\AbstractServiceTest;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserAuthenticationHandlerTest extends AbstractServiceTest
{
    /** @var MockObject */
    private $userRepository;

    /** @var UserPasswordEncoderInterface */
    private $passwordEncoder;

    /** @var UserAuthenticationHandler */
    private $authenticationHandler;


    protected function setUp()
    {
        parent::setUp();
        $this->initService();
    }


    private function initService()
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        $em = $this->createMock(EntityManagerInterface::class);
        $em->method("getRepository")->with(User::class)->willReturn($this->userRepository);

        $this->passwordEncoder = $this->getService("security.password_encoder");
        $formValidator = $this->getService("coloc_matching.core.form_validator");
        $dtoMapper = $this->getService("coloc_matching.core.user_dto_mapper");

        $this->authenticationHandler = new UserAuthenticationHandler($this->logger, $em, $dtoMapper,
            $formValidator, $this->passwordEncoder);
    }


    private function mockUser(string $username, string $rawPassword, string $status) : User
    {
        $user = new User($username, $rawPassword, "User", "Test");
        $user->setId(1);
        $user->setStatus($status);

        $password = $this->passwordEncoder->encodePassword($user, $rawPassword);
        $user->setPassword($password);

        $this->userRepository->expects(self::once())->method("findOneBy")->with(["email" => $username])->willReturn($user);

        return $user;
    }


    /**
     * @test
     * @throws \Exception
     */
    public function checkUserCredentials()
    {
        $username = "user@test.fr";
        $password = "secret123";
        $this->mockUser($username, $password, UserStatus::ENABLED);

        $authenticatedUser = $this->authenticationHandler->handleCredentials($username, $password);

        self::assertEquals($username, $authenticatedUser->getUsername(),
            "Expected to find user with username $username");
    }


    /**
     * @test
     * @throws \Exception
     */
    public function checkBannedUserCredentialsShouldThrowInvalidCredentials()
    {
        $username = "user@test.fr";
        $password = "secret123";
        $this->mockUser($username, $password, UserStatus::BANNED);

        $this->expectException(InvalidCredentialsException::class);

        $this->authenticationHandler->handleCredentials($username, $password);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function checkCredentialsWithWrongPasswordShouldThrowInvalidCredentials()
    {
        $username = "user@test.fr";
        $this->mockUser($username, "secret123", UserStatus::ENABLED);

        $this->expectException(InvalidCredentialsException::class);

        $this->authenticationHandler->handleCredentials($username, "uorauz");
    }


    /**
     * @test
     * @throws \Exception
     */
    public function checkEmptyCredentialsShouldThrowValidationErrors()
    {
        $username = "user@test.fr";

        $this->expectException(InvalidFormException::class);

        $this->authenticationHandler->handleCredentials($username, "");
    }


    /**
     * @test
     * @throws \Exception
     */
    public function checkNonExistingUserCredentialsShouldThrowInvalidCredentials()
    {
        $username = "user@test.fr";
        $this->userRepository
            ->expects(self::once())
            ->method("findOneBy")->with(["email" => $username])
            ->willReturn(null);

        $this->expectException(InvalidCredentialsException::class);

        $this->authenticationHandler->handleCredentials($username, "password");
    }

}
