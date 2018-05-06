<?php

namespace ColocMatching\RestBundle\Tests\Controller\Rest\v1\User;

use ColocMatching\CoreBundle\DAO\UserTokenDao;
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
        self::$client = self::initClient();
    }


    protected function clearData() : void
    {
        $this->userManager->deleteAll();

        /** @var UserTokenDao $userTokenDao */
        $userTokenDao = self::getService("coloc_matching.core.user_token_dao");
        $userTokenDao->deleteAll();
        $userTokenDao->flush();
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
    public function createUserWithSameEmailShouldReturn422()
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
        self::assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
    }


    /**
     * @test
     */
    public function createUserWithInvalidDataShouldReturn422()
    {
        $data = array (
            "email" => "",
            "plainPassword" => "Secret1234&",
            "firstName" => null,
            "lastName" => "Test",
            "type" => 5
        );

        static::$client->request("POST", "/rest/registrations", $data);
        self::assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
    }


    /**
     * @test
     *
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
     *
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
     *
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
     *
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

}
