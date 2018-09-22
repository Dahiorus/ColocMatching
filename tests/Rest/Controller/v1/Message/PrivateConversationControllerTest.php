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
        $author = $this->createUser("author@test.fr");
        self::$client = self::createAuthenticatedClient($author);

        $this->recipientId = $this->createUser("recipient@test.fr")->getId();
    }


    protected function clearData() : void
    {
        /** @var PrivateConversationDtoManagerInterface $conversationManager */
        $conversationManager = self::getService("coloc_matching.core.private_conversation_dto_manager");

        $conversationManager->deleteAll();
        $this->userManager->deleteAll();
    }


    /**
     * Creates a user with the specified email
     *
     * @param string $email The user email
     *
     * @return UserDto
     * @throws \Exception
     */
    private function createUser(string $email) : UserDto
    {
        $user = $this->userManager->create(array (
            "email" => $email,
            "plainPassword" => "secret1234",
            "firstName" => "User-" . rand(),
            "lastName" => "Test",
            "type" => "search"
        ));
        $user = $this->userManager->updateStatus($user, UserStatus::ENABLED);

        return $user;
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
        /** @var UserDto $recipient */
        $recipient = $this->userManager->read($this->recipientId);
        $this->userManager->updateStatus($recipient, UserStatus::BANNED);

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
