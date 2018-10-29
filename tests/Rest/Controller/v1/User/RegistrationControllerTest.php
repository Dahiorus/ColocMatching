<?php

namespace App\Tests\Rest\Controller\v1\User;

use App\Core\DTO\User\UserTokenDto;
use App\Core\Entity\User\UserStatus;
use App\Core\Entity\User\UserToken;
use App\Core\Entity\User\UserType;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Core\Manager\User\UserTokenDtoManagerInterface;
use App\Tests\Rest\AbstractControllerTest;
use Symfony\Component\HttpFoundation\Response;

class RegistrationControllerTest extends AbstractControllerTest
{
    /** @var UserTokenDtoManagerInterface */
    private $userTokenManager;

    /** @var UserDtoManagerInterface */
    private $userManager;


    protected function initServices() : void
    {
        $this->userManager = self::getService("coloc_matching.core.user_dto_manager");
        $this->userTokenManager = self::getService("coloc_matching.core.user_token_dto_manager");
    }


    protected function initTestData() : void
    {
        self::$client = static::initClient(array (), array ("HTTPS" => true));
    }


    protected function clearData() : void
    {
        $this->userManager->deleteAll();
        $this->userTokenManager->deleteAll();
    }


    /**
     * @throws \Exception
     */
    private function createUserToken() : UserTokenDto
    {
        $rawPwd = "password";
        $user = self::getService("coloc_matching.core.user_dto_manager")->create(array (
            "email" => "user-to-confirm@test.fr",
            "plainPassword" => array (
                "password" => $rawPwd,
                "confirmPassword" => $rawPwd,
            ),
            "type" => "proposal",
            "firstName" => "User",
            "lastName" => "Test"
        ));

        return self::getService("coloc_matching.core.user_token_dto_manager")
            ->create($user, UserToken::REGISTRATION_CONFIRMATION);
    }


    /**
     * @test
     */
    public function createUserShouldReturn201()
    {
        $rawPwd = "Secret123&";
        $data = array (
            "email" => "new-user@test.fr",
            "plainPassword" => array (
                "password" => $rawPwd,
                "confirmPassword" => $rawPwd,
            ),
            "firstName" => "User",
            "lastName" => "Test",
            "type" => UserType::SEARCH
        );

        static::$client->request("POST", "/rest/registrations", $data);
        self::assertStatusCode(Response::HTTP_CREATED);
        self::assertHasLocation();
    }


    /**
     * @test
     * @throws \Exception
     */
    public function createUserWithSameEmailShouldReturn400()
    {
        $rawPwd = "Secret123&";
        $data = array (
            "email" => "new-user@test.fr",
            "plainPassword" => array (
                "password" => $rawPwd,
                "confirmPassword" => $rawPwd,
            ),
            "firstName" => "New-User",
            "lastName" => "Test",
            "type" => UserType::SEARCH
        );
        self::getService("coloc_matching.core.user_dto_manager")->create($data);

        static::$client->request("POST", "/rest/registrations", $data);
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }


    /**
     * @test
     */
    public function createUserWithInvalidDataShouldReturn400()
    {
        $data = array (
            "email" => "",
            "plainPassword" => null,
            "firstName" => null,
            "lastName" => "Test",
            "type" => 5
        );

        static::$client->request("POST", "/rest/registrations", $data);
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function createUserAsAuthenticatedUserShouldReturn403()
    {
        $rawPwd = "password";
        $user = self::getService("coloc_matching.core.user_dto_manager")->create(array (
            "email" => "user-to-confirm@test.fr",
            "plainPassword" => array (
                "password" => $rawPwd,
                "confirmPassword" => $rawPwd,
            ),
            "type" => "proposal",
            "firstName" => "User",
            "lastName" => "Test"
        ));
        self::$client = self::createAuthenticatedClient($user, array (), array ("HTTPS" => true));

        static::$client->request("POST", "/rest/registrations");
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function confirmUserRegistrationShouldReturn200()
    {
        $userToken = $this->createUserToken();

        self::$client->request("POST", "/rest/registrations/confirmation",
            array ("value" => $userToken->getToken()));
        self::assertStatusCode(Response::HTTP_OK);

        $this->expectException(EntityNotFoundException::class);
        $this->userTokenManager->findByToken($userToken->getToken());

        self::assertEquals(UserStatus::ENABLED,
            $this->userManager->findByUsername($userToken->getToken())->getStatus(), "Expected user to be enabled");
    }


    /**
     * @test
     * @throws \Exception
     */
    public function confirmUserRegistrationWithEmptyTokenShouldReturn400()
    {
        self::$client->request("POST", "/rest/registrations/confirmation",
            array ("value" => null));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function confirmUserRegistrationWithUnknownTokenShouldReturn400()
    {
        self::$client->request("POST", "/rest/registrations/confirmation",
            array ("value" => "azertyuiop7852"));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function confirmUserRegistrationWithInvalidReasonTokenShouldReturn400()
    {
        $rawPwd = "password";
        $user = self::getService("coloc_matching.core.user_dto_manager")->create(array (
            "email" => "user@test.fr",
            "plainPassword" => array (
                "password" => $rawPwd,
                "confirmPassword" => $rawPwd,
            ),
            "type" => "proposal",
            "firstName" => "User",
            "lastName" => "Test"
        ));
        /** @var UserTokenDto $userToken */
        $userToken = self::getService("coloc_matching.core.user_token_dto_manager")
            ->create($user, UserToken::LOST_PASSWORD);

        self::$client->request("POST", "/rest/registrations/confirmation",
            array ("value" => $userToken->getToken()));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function confirmUserRegistrationAsAuthenticatedUserShouldReturn403()
    {
        $rawPwd = "password";
        $user = self::getService("coloc_matching.core.user_dto_manager")->create(array (
            "email" => "user-to-confirm@test.fr",
            "plainPassword" => array (
                "password" => $rawPwd,
                "confirmPassword" => $rawPwd,
            ),
            "type" => "proposal",
            "firstName" => "User",
            "lastName" => "Test"
        ));
        self::$client = self::createAuthenticatedClient($user, array (), array ("HTTPS" => true));

        static::$client->request("POST", "/rest/registrations/confirmation");
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }

}
