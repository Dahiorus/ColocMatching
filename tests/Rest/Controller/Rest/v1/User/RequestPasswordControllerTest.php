<?php

namespace App\Tests\Rest\Controller\Rest\v1\User;

use App\Core\Entity\User\UserToken;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Core\Manager\User\UserTokenDtoManagerInterface;
use App\Tests\Rest\AbstractControllerTest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class RequestPasswordControllerTest extends AbstractControllerTest
{
    /** @var UserDtoManagerInterface */
    private $userManager;

    /** @var UserTokenDtoManagerInterface */
    private $userTokenManager;


    protected function initServices() : void
    {
        $this->userManager = self::getService("coloc_matching.core.user_dto_manager");
        $this->userTokenManager = self::getService("coloc_matching.core.user_token_dto_manager");
    }


    protected function initTestData() : void
    {
        $this->userManager->create(array (
            "email" => "user@test.fr",
            "plainPassword" => "password",
            "firstName" => "User",
            "lastName" => "Test",
            "type" => "proposal"
        ));

        self::$client = static::initClient(array (), array ("HTTPS" => true));
    }


    protected function clearData() : void
    {
        $this->userManager->deleteAll();
        $this->userTokenManager->deleteAll();
    }


    /**
     * @test
     */
    public function requestPasswordShouldReturn201()
    {
        self::$client->request("POST", "/rest/passwords/request", array ("email" => "user@test.fr"));
        self::assertStatusCode(Response::HTTP_CREATED);
    }


    /**
     * @test
     */
    public function requestPasswordForNonExistingUserShouldReturn400()
    {
        self::$client->request("POST", "/rest/passwords/request", array ("email" => "other@test.fr"));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }


    /**
     * @test
     */
    public function requestPasswordWithInvalidDataShouldReturn400()
    {
        self::$client->request("POST", "/rest/passwords/request", array ("email" => "qjhjdksdkhjfq"));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }


    /**
     * @test
     */
    public function requestPasswordTwiceShouldReturn400()
    {
        self::$client->request("POST", "/rest/passwords/request", array ("email" => "user@test.fr"));
        self::$client->request("POST", "/rest/passwords/request", array ("email" => "user@test.fr"));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function requestPasswordAsAuthenticatedUserShouldReturn403()
    {
        $user = $this->userManager->findByUsername("user@test.fr");
        self::$client = self::createAuthenticatedClient($user, array (), array ("HTTPS" => true));

        self::$client->request("POST", "/rest/passwords/request", array ("email" => "user@test.fr"));
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }


    /**
     * @test
     */
    public function renewPasswordShouldReturn200()
    {
        self::$client->request("POST", "/rest/passwords/request", array ("email" => "user@test.fr"));

        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getService("doctrine.orm.entity_manager");
        /** @var UserToken $userToken */
        $userToken = $entityManager->getRepository(UserToken::class)
            ->findOneBy(array ("username" => "user@test.fr", "reason" => UserToken::LOST_PASSWORD));

        self::$client->request("POST", "/rest/passwords",
            array ("token" => $userToken->getToken(), "newPassword" => "new_password"));
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function renewPasswordWithInvalidDataShouldReturn400()
    {
        self::$client->request("POST", "/rest/passwords",
            array ("token" => null, "newPassword" => "new_password"));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }


    /**
     * @test
     */
    public function renewPasswordWithInvalidTokenShouldReturn400()
    {
        self::$client->request("POST", "/rest/passwords",
            array ("token" => "qjskdhfslkjfhsf", "newPassword" => "new_password"));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }


    /**
     * @test
     */
    public function renewPasswordTwiceShouldReturn400()
    {
        self::$client->request("POST", "/rest/passwords/request", array ("email" => "user@test.fr"));

        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getService("doctrine.orm.entity_manager");
        /** @var UserToken $userToken */
        $userToken = $entityManager->getRepository(UserToken::class)
            ->findOneBy(array ("username" => "user@test.fr", "reason" => UserToken::LOST_PASSWORD));

        self::$client->request("POST", "/rest/passwords",
            array ("token" => $userToken->getToken(), "newPassword" => "new_password"));
        self::$client->request("POST", "/rest/passwords",
            array ("token" => $userToken->getToken(), "newPassword" => "new_password"));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function renewPasswordAsAuthenticatedUserShouldReturn403()
    {
        $user = $this->userManager->findByUsername("user@test.fr");
        self::$client = self::createAuthenticatedClient($user, array (), array ("HTTPS" => true));

        self::$client->request("POST", "/rest/passwords");
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }

}
