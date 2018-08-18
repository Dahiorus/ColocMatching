<?php

namespace App\Tests\Core\Service;

use App\Core\DTO\User\UserDto;
use App\Core\DTO\User\UserTokenDto;
use App\Core\Entity\User\User;
use App\Core\Entity\User\UserToken;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Exception\InvalidFormException;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Core\Manager\User\UserTokenDtoManagerInterface;
use App\Core\Service\PasswordRequester;
use App\Core\Service\UserTokenGenerator;
use App\Tests\Core\AbstractServiceTest;

class PasswordRequesterTest extends AbstractServiceTest
{
    /** @var PasswordRequester */
    private $passwordRequester;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $userManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $userTokenManager;


    protected function setUp()
    {
        parent::setUp();

        $this->userManager = $this->createMock(UserDtoManagerInterface::class);
        $this->userTokenManager = $this->createMock(UserTokenDtoManagerInterface::class);

        $this->passwordRequester = new PasswordRequester($this->logger, $this->userManager, $this->userTokenManager,
            $this->getService("coloc_matching.core.form_validator"), $this->getService("coloc_matching.core.mailer"),
            $this->getService("router"));
    }


    /**
     * Creates a LOST_PASSWORD user token from the specified email
     *
     * @param string $email The email
     *
     * @return UserTokenDto
     */
    private function createUserToken(string $email) : UserTokenDto
    {
        $userToken = (new UserTokenGenerator())->generateToken($email, UserToken::LOST_PASSWORD);

        $dto = new UserTokenDto();
        $dto->setToken($userToken->getToken())->setUsername($userToken->getUsername())
            ->setReason($userToken->getReason());

        return $dto;
    }


    /**
     * @test
     *
     * @throws \Exception
     */
    public function requestPasswordShouldCreateUserToken()
    {
        $user = new UserDto();
        $user->setEmail("user@test.fr");
        $user->setFirstName("User");
        $user->setLastName("Test");
        $this->userManager->expects(self::once())->method("findByUsername")->with($user->getEmail())->willReturn($user);
        $this->userTokenManager->expects(self::once())->method("create")->with($user, UserToken::LOST_PASSWORD)
            ->willReturn($this->createUserToken($user->getEmail()));

        $this->passwordRequester->requestPassword(array ("email" => $user->getEmail()));
    }


    /**
     * @test
     *
     * @throws \Exception
     */
    public function requestPasswordForNonExistingUserShouldThrowEntityNotFound()
    {
        $email = "user@test.fr";

        $this->userManager->expects(self::once())->method("findByUsername")->with($email)
            ->willThrowException(new EntityNotFoundException(User::class, "email", $email));

        $this->expectException(EntityNotFoundException::class);

        $this->passwordRequester->requestPassword(array ("email" => $email));
    }


    /**
     * @test
     *
     * @throws \Exception
     */
    public function requestPasswordWithInvalidDataShouldThrowInvalidForm()
    {
        $this->expectException(InvalidFormException::class);

        $this->passwordRequester->requestPassword(array ());
    }


    /**
     * @test
     *
     * @throws \Exception
     */
    public function updatePassword()
    {
        $user = new UserDto();
        $user->setEmail("user@test.fr");
        $user->setFirstName("User");
        $user->setLastName("Test");
        $userToken = $this->createUserToken($user->getEmail());

        $data = array ("token" => $userToken->getToken(), "newPassword" => "new_password");

        $this->userTokenManager->expects(self::once())->method("findByToken")
            ->with($userToken->getToken(), UserToken::LOST_PASSWORD)
            ->willReturn($userToken);
        $this->userTokenManager->expects(self::once())->method("delete")->with($userToken);
        $this->userManager->expects(self::once())->method("findByUsername")
            ->with($userToken->getUsername())
            ->willReturn($user);
        $this->userManager->expects(self::once())->method("update")
            ->with($user, array ("plainPassword" => $data["newPassword"]))
            ->willReturn($user->setPlainPassword($data["newPassword"]));

        $this->passwordRequester->updatePassword($data);
    }


    /**
     * @test
     *
     * @throws \Exception
     */
    public function updatePasswordWithInvalidDataShouldThrowInvalidForm()
    {
        $this->expectException(InvalidFormException::class);

        $this->passwordRequester->updatePassword(array ("token" => ""));
    }


    /**
     * @test
     *
     * @throws \Exception
     */
    public function updatePasswordWithNonExistingTokenShouldThrowEntityNotFound()
    {
        $data = array ("token" => "kdlkfqfhqsdjflhqsdjflhq", "newPassword" => "new_password");

        $this->userTokenManager->expects(self::once())->method("findByToken")
            ->with($data["token"], UserToken::LOST_PASSWORD)
            ->willThrowException(new EntityNotFoundException(UserToken::class, "token", $data["token"]));

        $this->expectException(EntityNotFoundException::class);

        $this->passwordRequester->updatePassword($data);
    }

}
