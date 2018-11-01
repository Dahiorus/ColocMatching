<?php

namespace App\Tests\Rest\Controller\v1\User;

use App\Core\Entity\User\UserStatus;
use App\Core\Entity\User\UserType;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Tests\Rest\AbstractControllerTest;
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
        $user = $this->createSearchUser(self::getService("coloc_matching.core.user_dto_manager"), "user@test.fr");
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
        self::$client->request("PATCH", "/rest/me/status", array ("value" => UserStatus::ENABLED));
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function updateSelfStatusWithInvalidValueShouldReturn400()
    {
        self::$client->request("PATCH", "/rest/me/status", array ("value" => UserStatus::BANNED));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }


    /**
     * @test
     */
    public function updateSelfPasswordShouldReturn200()
    {
        $rawPwd = "new_password";
        self::$client->request("POST", "/rest/me/password", array (
            "oldPassword" => "Secret&1234",
            "newPassword" => array (
                "password" => $rawPwd,
                "confirmPassword" => $rawPwd)
        ));
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function updateSelfPasswordWithInvalidDataShouldReturn400()
    {
        $rawPwd = "new_password";
        self::$client->request("POST", "/rest/me/password", array (
            "oldPassword" => "Secret",
            "newPassword" => array (
                "password" => $rawPwd,
                "confirmPassword" => $rawPwd)
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
            "type" => UserType::PROPOSAL
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
            "type" => UserType::PROPOSAL
        ));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }


    /**
     * @test
     */
    public function patchSelfShouldReturn200()
    {
        self::$client->request("PATCH", "/rest/me", array (
            "type" => UserType::PROPOSAL
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
