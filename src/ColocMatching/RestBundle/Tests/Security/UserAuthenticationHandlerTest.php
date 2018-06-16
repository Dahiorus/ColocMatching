<?php

namespace ColocMatching\RestBundle\Tests\Security;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidCredentialsException;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Manager\User\UserDtoManager;
use ColocMatching\CoreBundle\Mapper\User\UserDtoMapper;
use ColocMatching\CoreBundle\Tests\AbstractServiceTest;
use ColocMatching\CoreBundle\Validator\FormValidator;
use ColocMatching\RestBundle\Security\UserAuthenticationHandler;
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
