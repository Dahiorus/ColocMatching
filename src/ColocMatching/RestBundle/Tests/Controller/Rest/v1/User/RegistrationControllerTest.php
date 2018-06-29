<?php

namespace ColocMatching\RestBundle\Tests\Controller\Rest\v1\User;

use ColocMatching\CoreBundle\DTO\User\UserTokenDto;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Entity\User\UserToken;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
use ColocMatching\CoreBundle\Manager\User\UserTokenDtoManagerInterface;
use ColocMatching\RestBundle\Tests\AbstractControllerTest;
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
        $user = $this->userManager->create(array (
            "email" => "user-to-confirm@test.fr",
            "plainPassword" => "password",
            "type" => "proposal",
            "firstName" => "User",
            "lastName" => "Test"
        ));

        return $this->userTokenManager->create($user, UserToken::REGISTRATION_CONFIRMATION);
    }


    /**
     * @test
     */
    public function createUserShouldReturn201()
    {
        $data = array (
            "email" => "new-user@test.fr",
            "plainPassword" => "Secret1234&",
            "firstName" => "User",
            "lastName" => "Test",
            "type" => UserConstants::TYPE_SEARCH
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
        $data = array (
            "email" => "new-user@test.fr",
            "plainPassword" => "Secret1234&",
            "firstName" => "New-User",
            "lastName" => "Test",
            "type" => UserConstants::TYPE_SEARCH
        );
        $this->userManager->create($data);

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
            "plainPassword" => "Secret1234&",
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
        $user = $this->userManager->create(array (
            "email" => "user-to-confirm@test.fr",
            "plainPassword" => "password",
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

        self::assertEquals(UserConstants::STATUS_ENABLED,
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
        $user = $this->userManager->create(array (
            "email" => "user@test.fr",
            "plainPassword" => "password",
            "type" => "proposal",
            "firstName" => "User",
            "lastName" => "Test"
        ));
        $userToken = $this->userTokenManager->create($user, UserToken::LOST_PASSWORD);

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
        $user = $this->userManager->create(array (
            "email" => "user-to-confirm@test.fr",
            "plainPassword" => "password",
            "type" => "proposal",
            "firstName" => "User",
            "lastName" => "Test"
        ));
        self::$client = self::createAuthenticatedClient($user, array (), array ("HTTPS" => true));

        static::$client->request("POST", "/rest/registrations/confirmation");
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }

}
