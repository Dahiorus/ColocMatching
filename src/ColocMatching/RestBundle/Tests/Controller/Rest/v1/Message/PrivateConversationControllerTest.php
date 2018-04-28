<?php

namespace ColocMatching\RestBundle\Tests\Controller\Rest\v1\Message;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Manager\Message\PrivateConversationDtoManagerInterface;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
use ColocMatching\RestBundle\Tests\AbstractControllerTest;
use Symfony\Component\HttpFoundation\Response;

class PrivateConversationControllerTest extends AbstractControllerTest
{
    /** @var UserDtoManagerInterface */
    private $userManager;

    /** @var integer */
    private $recipientId;


    /**
     * @throws \Exception
     */
    protected function setUp()
    {
        parent::setUp();
        $this->userManager = self::getService("coloc_matching.core.user_dto_manager");

        $author = $this->createUser("author@test.fr");
        self::$client = self::createAuthenticatedClient($author);

        $this->recipientId = $this->createUser("recipient@test.fr")->getId();
    }


    protected function tearDown()
    {
        /** @var PrivateConversationDtoManagerInterface $conversationManager */
        $conversationManager = self::getService("coloc_matching.core.private_conversation_dto_manager");

        $conversationManager->deleteAll(false);
        $this->userManager->deleteAll();
        parent::tearDown();
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
        $user = $this->userManager->updateStatus($user, UserConstants::STATUS_ENABLED);

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
    public function postMessageWithInvalidDataShouldReturn422()
    {
        self::$client->request("POST", "/rest/users/" . $this->recipientId . "/messages", array (
            "unknown" => null
        ));
        self::assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function postMessageToInvalidUserShouldReturn400()
    {
        /** @var UserDto $recipient */
        $recipient = $this->userManager->read($this->recipientId);
        $this->userManager->updateStatus($recipient, UserConstants::STATUS_BANNED);

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
