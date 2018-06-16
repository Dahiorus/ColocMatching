<?php

namespace ColocMatching\RestBundle\Tests\Controller\Rest\v1\User;

use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
use ColocMatching\RestBundle\Tests\AbstractControllerTest;
use Symfony\Component\HttpFoundation\Response;

class SelfControllerTest extends AbstractControllerTest
{
    /** @var UserDtoManagerInterface */
    private $userManager;


    protected function initServices() : void
    {
        /** @var UserDtoManagerInterface $userManager */
        $this->userManager = self::getService("coloc_matching.core.user_dto_manager");
    }


    protected function initTestData() : void
    {
        $user = $this->userManager->create(array (
            "email" => "user@test.fr",
            "plainPassword" => "Secret1234&",
            "firstName" => "User",
            "lastName" => "Test",
            "type" => UserConstants::TYPE_SEARCH
        ));

        self::$client = self::createAuthenticatedClient($user);
    }


    protected function clearData() : void
    {
        $this->userManager->deleteAll();
    }


    /**
     * @test
     */
    public function getSelfShouldReturn200()
    {
        self::$client->request("GET", "/rest/me");
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function getSelfAsAnonymousShouldReturn401()
    {
        self::$client = self::initClient();

        self::$client->request("GET", "/rest/me");
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }


    /**
     * @test
     */
    public function updateSelfStatusShouldReturn200()
    {
        self::$client->request("PATCH", "/rest/me/status", array ("value" => UserConstants::STATUS_ENABLED));
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function updateSelfStatusWithInvalidValueShouldReturn400()
    {
        self::$client->request("PATCH", "/rest/me/status", array ("value" => UserConstants::STATUS_BANNED));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }


    /**
     * @test
     */
    public function updateSelfPasswordShouldReturn200()
    {
        self::$client->request("POST", "/rest/me/password", array (
            "oldPassword" => "Secret1234&",
            "newPassword" => "new_password"
        ));
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function updateSelfPasswordWithInvalidDataShouldReturn400()
    {
        self::$client->request("POST", "/rest/me/password", array (
            "oldPassword" => "Secret",
            "newPassword" => "new_password"
        ));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }


    /**
     * @test
     */
    public function putSelfActionShouldReturn200()
    {
        self::$client->request("PUT", "/rest/me", array (
            "email" => "user@test.fr",
            "firstName" => "User",
            "lastName" => "Test",
            "type" => UserConstants::TYPE_PROPOSAL
        ));
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function putSelfActionWithInvalidDataShouldReturn200()
    {
        self::$client->request("PUT", "/rest/me", array (
            "email" => "user@test.fr",
            "firstName" => "User",
            "lastName" => "",
            "type" => UserConstants::TYPE_PROPOSAL
        ));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }


    /**
     * @test
     */
    public function patchSelfShouldReturn200()
    {
        self::$client->request("PATCH", "/rest/me", array (
            "type" => UserConstants::TYPE_PROPOSAL
        ));
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function patchSelfWithInvalidShouldReturn200()
    {
        self::$client->request("PATCH", "/rest/me", array (
            "type" => 51
        ));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }

}
