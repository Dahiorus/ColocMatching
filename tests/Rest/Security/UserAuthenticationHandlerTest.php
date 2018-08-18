<?php

namespace App\Tests\Rest\Security;

use App\Core\DTO\User\UserDto;
use App\Core\Entity\User\User;
use App\Core\Entity\User\UserConstants;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Exception\InvalidCredentialsException;
use App\Core\Exception\InvalidFormException;
use App\Core\Manager\User\UserDtoManager;
use App\Core\Mapper\User\UserDtoMapper;
use App\Core\Validator\FormValidator;
use App\Rest\Security\UserAuthenticationHandler;
use App\Tests\Core\AbstractServiceTest;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserAuthenticationHandlerTest extends AbstractServiceTest
{
    /** @var UserAuthenticationHandler */
    private $authenticationHandler;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $userManager;

    /** @var UserPasswordEncoderInterface */
    private $passwordEncoder;

    /** @var UserDtoMapper */
    private $userDtoMapper;

    /** @var FormValidator */
    private $formValidator;


    protected function setUp()
    {
        parent::setUp();
        $this->initService();
    }


    private function initService()
    {
        $this->userManager = $this->createMock(UserDtoManager::class);
        $this->passwordEncoder = $this->getService("security.password_encoder");
        $this->userDtoMapper = $this->getService("coloc_matching.core.user_dto_mapper");
        $this->formValidator = $this->getService("coloc_matching.core.form_validator");

        $this->authenticationHandler = new UserAuthenticationHandler($this->logger, $this->userManager,
            $this->userDtoMapper, $this->formValidator, $this->passwordEncoder);
    }


    private function mockUser(string $username, string $rawPassword, string $status) : UserDto
    {
        $user = new User($username, $rawPassword, "User", "Test");
        $user->setId(1);
        $user->setStatus($status);

        $password = $this->passwordEncoder->encodePassword($user, $rawPassword);
        $user->setPassword($password);

        $dto = $this->userDtoMapper->toDto($user);

        $this->userManager->expects(self::once())->method("findByUsername")->with($username)->willReturn($dto);

        return $dto;
    }


    /**
     * @test
     * @throws \Exception
     */
    public function checkUserCredentials()
    {
        $username = "user@test.fr";
        $password = "secret123";

        $user = $this->mockUser($username, $password, UserConstants::STATUS_ENABLED);
        $this->userManager->expects(self::once())->method("update")->willReturn($user);

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

        $this->mockUser($username, $password, UserConstants::STATUS_BANNED);

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
        $this->mockUser($username, "secret123", UserConstants::STATUS_ENABLED);

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
        $this->userManager
            ->expects(self::once())
            ->method("findByUsername")->with($username)
            ->willThrowException(new EntityNotFoundException(User::class, "username", $username));

        $this->expectException(InvalidCredentialsException::class);

        $this->authenticationHandler->handleCredentials($username, "password");
    }

}
