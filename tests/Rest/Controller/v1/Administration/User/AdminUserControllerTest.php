<?php

namespace App\Tests\Rest\Controller\v1\Administration\User;

use App\Core\DTO\User\UserDto;
use App\Core\Entity\User\UserStatus;
use App\Core\Entity\User\UserType;
use App\Core\Manager\Message\PrivateConversationDtoManagerInterface;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Core\Manager\Visit\VisitDtoManagerInterface;
use App\Tests\Rest\AbstractControllerTest;
use Symfony\Component\HttpFoundation\Response;

class AdminUserControllerTest extends AbstractControllerTest
{
    /** @var UserDtoManagerInterface */
    private $userManager;

    /** @var UserDto */
    private $userTest;


    protected function initServices() : void
    {
        $this->userManager = self::getService("coloc_matching.core.user_dto_manager");
    }


    protected function initTestData() : void
    {
        $this->userTest = $this->createProposalUser($this->userManager, "search@test.fr");
        $admin = $this->createAdmin($this->userManager);

        self::$client = self::createAuthenticatedClient($admin);
    }


    protected function clearData() : void
    {
        $this->userManager->deleteAll();
    }


    /**
     * @test
     */
    public function putUserShouldReturn200()
    {
        self::$client->request("PUT", "/rest/admin/users/" . $this->userTest->getId(), array (
            "email" => $this->userTest->getEmail(),
            "firstName" => $this->userTest->getFirstName(),
            "plainPassword" => "Secret1234&",
            "lastName" => $this->userTest->getLastName(),
            "type" => UserType::PROPOSAL
        ));
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function putUserWithInvalidDataShouldReturn400()
    {
        self::$client->request("PUT", "/rest/admin/users/" . $this->userTest->getId(), array (
            "email" => null,
            "firstName" => $this->userTest->getFirstName(),
            "lastName" => $this->userTest->getLastName(),
            "type" => 50
        ));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }


    /**
     * @test
     */
    public function putUserAsAnonymousShouldReturn401()
    {
        self::$client = self::initClient();
        self::$client->request("PUT", "/rest/admin/users/" . $this->userTest->getId(), array (
            "email" => $this->userTest->getEmail(),
            "firstName" => $this->userTest->getFirstName(),
            "lastName" => $this->userTest->getLastName(),
            "type" => $this->userTest->getType()
        ));
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }


    /**
     * @test
     */
    public function putNonExistingUserShouldReturn404()
    {
        self::$client->request("PUT", "/rest/admin/users/0", array (
            "email" => $this->userTest->getEmail(),
            "firstName" => $this->userTest->getFirstName(),
            "lastName" => $this->userTest->getLastName(),
            "type" => $this->userTest->getType()
        ));
        self::assertStatusCode(Response::HTTP_NOT_FOUND);
    }


    /**
     * @test
     */
    public function patchUserShouldReturn200()
    {
        self::$client->request("PATCH", "/rest/admin/users/" . $this->userTest->getId(), array (
            "type" => UserType::PROPOSAL
        ));
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function patchUserWithInvalidDataShouldReturn400()
    {
        self::$client->request("PATCH", "/rest/admin/users/" . $this->userTest->getId(), array (
            "email" => null,
            "type" => 50
        ));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }


    /**
     * @test
     */
    public function patchUserAsAnonymousShouldReturn401()
    {
        self::$client = self::initClient();
        self::$client->request("PATCH", "/rest/admin/users/" . $this->userTest->getId(), array (
            "type" => $this->userTest->getType()
        ));
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }


    /**
     * @test
     */
    public function patchNonExistingUserShouldReturn404()
    {
        self::$client->request("PATCH", "/rest/admin/users/0", array ());
        self::assertStatusCode(Response::HTTP_NOT_FOUND);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function updateUserStatusShouldReturn200()
    {
        self::$client->request("PATCH", "/rest/admin/users/" . $this->userTest->getId() . "/status", array (
            "value" => UserStatus::ENABLED
        ));
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function updateNonExistingUserStatusShouldReturn404()
    {
        self::$client->request("PATCH", "/rest/admin/users/0/status", array (
            "value" => UserStatus::ENABLED
        ));
        self::assertStatusCode(Response::HTTP_NOT_FOUND);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function updateUserStatusWithInvalidValueShouldReturn400()
    {
        self::$client->request("PATCH", "/rest/admin/users/" . $this->userTest->getId() . "/status", array (
            "value" => "unknown"
        ));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }


    /**
     * @test
     */
    public function updateUserStatusAsNonAPIUserShouldReturn403()
    {
        self::$client = self::createAuthenticatedClient($this->userTest);

        self::$client->request("PATCH", "/rest/admin/users/" . $this->userTest->getId() . "/status", array (
            "value" => UserStatus::ENABLED
        ));
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }


    /**
     * @test
     */
    public function updateUserStatusAsAnonymousShouldReturn401()
    {
        self::$client = self::initClient();

        self::$client->request("PATCH", "/rest/admin/users/" . $this->userTest->getId() . "/status", array (
            "value" => UserStatus::ENABLED
        ));
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function deleteUserShouldReturn204()
    {
        self::$client->request("DELETE", "/rest/admin/users/" . $this->userTest->getId());
        self::assertStatusCode(Response::HTTP_NO_CONTENT);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function deleteUserWithVisitsShouldReturn204()
    {
        /** @var VisitDtoManagerInterface $visitManager */
        $visitManager = self::getService("coloc_matching.core.visit_dto_manager");
        $visitManager->create(
            $this->createSearchUser($this->userManager, "visitor@yopmail.com", UserStatus::ENABLED), $this->userTest);
        $visitManager->create($this->userTest,
            $this->createSearchUser($this->userManager, "visited@yopmail.com", UserStatus::ENABLED));

        self::$client->request("DELETE", "/rest/admin/users/" . $this->userTest->getId());
        self::assertStatusCode(Response::HTTP_NO_CONTENT);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function deleteUserHavingPrivateConversationsShouldReturn204()
    {
        /** @var PrivateConversationDtoManagerInterface $conversationManager */
        $conversationManager = self::getService("coloc_matching.core.private_conversation_dto_manager");
        $conversationManager->createMessage(
            $this->userTest,
            $this->createSearchUser($this->userManager, "recipient@yopmail.com", UserStatus::ENABLED),
            ["content" => "message test"]);

        self::$client->request("DELETE", "/rest/admin/users/" . $this->userTest->getId());
        self::assertStatusCode(Response::HTTP_NO_CONTENT);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function deleteNonExistingUserShouldReturn204()
    {
        self::$client->request("DELETE", "/rest/admin/users/0");
        self::assertStatusCode(Response::HTTP_NO_CONTENT);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function deleteUserAsAnonymousShouldReturn401()
    {
        self::$client = self::initClient();
        self::$client->request("DELETE", "/rest/admin/users/" . $this->userTest->getId());
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }

}
