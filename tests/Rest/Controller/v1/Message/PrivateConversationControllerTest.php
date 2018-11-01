<?php

namespace App\Tests\Rest\Controller\v1\Message;

use App\Core\DTO\User\UserDto;
use App\Core\Entity\User\UserStatus;
use App\Core\Manager\Message\PrivateConversationDtoManagerInterface;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Tests\Rest\AbstractControllerTest;
use Symfony\Component\HttpFoundation\Response;

class PrivateConversationControllerTest extends AbstractControllerTest
{
    /** @var UserDtoManagerInterface */
    private $userManager;

    /** @var integer */
    private $recipientId;


    protected function initServices() : void
    {
        $this->userManager = self::getService("coloc_matching.core.user_dto_manager");
    }


    protected function initTestData() : void
    {
        $author = $this->createSearchUser($this->userManager, "author@test.fr", UserStatus::ENABLED);
        $this->recipientId = $this->createProposalUser($this->userManager, "recipient@test.fr",
            UserStatus::ENABLED)->getId();

        self::$client = self::createAuthenticatedClient($author);
    }


    protected function clearData() : void
    {
        /** @var PrivateConversationDtoManagerInterface $conversationManager */
        $conversationManager = self::getService("coloc_matching.core.private_conversation_dto_manager");

        $conversationManager->deleteAll();
        $this->userManager->deleteAll();
    }


    /**
     * @test
     */
    public function postMessageShouldReturn201()
    {
        self::$client->request("POST", "/rest/users/" . $this->recipientId . "/messages", array (
            "content" => "&é'(-è_çà)="
        ));
        self::assertStatusCode(Response::HTTP_CREATED);
    }


    /**
     * @test
     */
    public function postMessageWithInvalidDataShouldReturn400()
    {
        self::$client->request("POST", "/rest/users/" . $this->recipientId . "/messages", array (
            "unknown" => null
        ));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function postMessageToInvalidUserShouldReturn400()
    {
        $userManager = self::getService("coloc_matching.core.user_dto_manager");

        /** @var UserDto $recipient */
        $recipient = $userManager->read($this->recipientId);
        $userManager->updateStatus($recipient, UserStatus::BANNED);

        self::$client->request("POST", "/rest/users/" . $this->recipientId . "/messages", array (
            "content" => "&é'(-è_çà)="
        ));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }


    /**
     * @test
     */
    public function postMessageAsAnonymousShouldReturn401()
    {
        self::$client = self::initClient();
        self::$client->request("POST", "/rest/users/" . $this->recipientId . "/messages", array (
            "content" => "&é'(-è_çà)="
        ));
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }


    /**
     * @test
     */
    public function getMessagesShouldReturn200()
    {
        self::$client->request("GET", "/rest/users/" . $this->recipientId . "/messages");
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function getMessagesWithUnknownUsersShouldReturn404()
    {
        self::$client->request("GET", "/rest/users/0/messages");
        self::assertStatusCode(Response::HTTP_NOT_FOUND);
    }


    /**
     * @test
     */
    public function getMessagesAsAnonymousShouldReturn404()
    {
        self::$client = self::initClient();
        self::$client->request("GET", "/rest/users/" . $this->recipientId . "/messages");
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }

}
