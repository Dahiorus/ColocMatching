<?php

namespace ColocMatching\RestBundle\Tests\Controller\Rest\v1\Message;

use ColocMatching\CoreBundle\Entity\User\PrivateConversation;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Exception\InvalidRecipientException;
use ColocMatching\CoreBundle\Exception\UserNotFoundException;
use ColocMatching\CoreBundle\Form\Type\Message\MessageType;
use ColocMatching\CoreBundle\Manager\Message\PrivateConversationManager;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Tests\Utils\Mock\Message\PrivateConversationMock;
use ColocMatching\CoreBundle\Tests\Utils\Mock\User\UserMock;
use ColocMatching\RestBundle\Tests\Controller\Rest\v1\RestTestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

class PrivateConversationControllerTest extends RestTestCase {

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $conversationManager;

    /**
     * @var User
     */
    private $authenticatedUser;

    /**
     * @var User
     */
    private $user;

    /**
     * @var PrivateConversation
     */
    private $conversation;


    protected function setUp() {
        parent::setUp();

        $this->logger = $this->client->getContainer()->get("logger");

        $this->conversationManager = $this->createMock(PrivateConversationManager::class);
        $this->client->getContainer()->set("coloc_matching.core.private_conversation_manager",
            $this->conversationManager);

        $this->authenticatedUser = UserMock::createUser(1, "auth-user@test.fr", "password", "Auth", "Test",
            UserConstants::TYPE_SEARCH);

        $this->user = UserMock::createUser(2, "user@test.fr", "secret", "User", "Participant",
            UserConstants::TYPE_PROPOSAL);
        $this->userManager->method("read")->with($this->user->getId())->willReturn($this->user);

        $this->createConversation($this->authenticatedUser, $this->user);
        $this->setAuthenticatedRequest($this->authenticatedUser);
    }


    protected function tearDown() {
        $this->logger->info("Test end");
    }


    public function testGetMessagesActionWith200() {
        $this->logger->info("Test getting messages between the authenticated user and an other with status code 200");

        $filter = new PageableFilter();
        $filter->setSize(25);
        $filter->setOrder(PageableFilter::ORDER_ASC);
        $filter->setSort("createdAt");

        $this->conversationManager->expects(self::once())->method("listMessages")
            ->with($this->user, $this->authenticatedUser, $filter)
            ->willReturn(array_slice($this->conversation->getMessages()->toArray(), $filter->getOffset(),
                $filter->getSize()));
        $this->conversationManager->expects(self::once())->method("countMessages")->with($this->user,
            $this->authenticatedUser)->willReturn($this->conversation->getMessages()->count());

        $this->client->request("GET", sprintf("/rest/users/%d/messages", $this->user->getId()), array ("size" => 25));
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_OK, $response["code"]);
        self::assertNotEmpty($response["rest"]["content"]);
    }


    public function testGetMessagesActionWith206() {
        $this->logger->info("Test getting messages between the authenticated user and an other with status code 206");

        $filter = new PageableFilter();
        $filter->setOrder(PageableFilter::ORDER_ASC);
        $filter->setSort("createdAt");

        $this->conversationManager->expects(self::once())->method("listMessages")
            ->with($this->user, $this->authenticatedUser, $filter)
            ->willReturn(array_slice($this->conversation->getMessages()->toArray(), $filter->getOffset(),
                $filter->getSize()));
        $this->conversationManager->expects(self::once())->method("countMessages")->with($this->user,
            $this->authenticatedUser)->willReturn($this->conversation->getMessages()->count());

        $this->client->request("GET", sprintf("/rest/users/%d/messages", $this->user->getId()));
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_PARTIAL_CONTENT, $response["code"]);
        self::assertNotEmpty($response["rest"]["content"]);
    }


    public function testGetMessagesActionWith404() {
        $this->logger->info("Test getting messages between the authenticated user and a non existing user");

        $this->userManager->expects(self::once())->method("read")->with($this->user->getId())
            ->willThrowException(new UserNotFoundException("id", $this->user->getId()));
        $this->conversationManager->expects(self::never())->method("listMessages");
        $this->conversationManager->expects(self::never())->method("countMessages");

        $this->client->request("GET", sprintf("/rest/users/%d/messages", $this->user->getId()));
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testPostMessageActionWith201() {
        $this->logger->info("Test posting a new message to a user with status 201");

        $data = array ("content" => "This is a new message");
        $message = PrivateConversationMock::createMessage($this->conversation->getMessages()->count() + 1,
            $this->authenticatedUser, $this->conversation, $data["content"]);

        $this->conversationManager->expects(self::once())->method("createMessage")->with($this->authenticatedUser,
            $this->user, $data)->willReturn($message);

        $this->client->request("POST", sprintf("/rest/users/%d/messages", $this->user->getId()), $data);
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_CREATED, $response["code"]);
    }


    public function testPostMessageActionWith422() {
        $this->logger->info("Test posting a new message to a user with status 422");

        $data = array ("content" => null);

        $this->conversationManager->expects(self::once())->method("createMessage")->with($this->authenticatedUser,
            $this->user, $data)->willThrowException(new InvalidFormException("Exception from test",
            $this->getForm(MessageType::class)->getErrors()));

        $this->client->request("POST", sprintf("/rest/users/%d/messages", $this->user->getId()), $data);
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response["code"]);
    }


    public function testPostMessageActionWith400() {
        $this->logger->info("Test posting a new message to himself");

        $data = array ("content" => "Message content");

        $this->conversationManager->expects(self::once())->method("createMessage")
            ->with($this->authenticatedUser, $this->user, $data)
            ->willThrowException(new InvalidRecipientException($this->authenticatedUser));

        $this->client->request("POST", sprintf("/rest/users/%d/messages", $this->user->getId()), $data);
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_BAD_REQUEST, $response["code"]);
    }


    public function testPostMessageActionWith404() {
        $this->logger->info("Test posting a new message to a non existing user");

        $this->userManager->expects(self::once())->method("read")->with($this->user->getId())
            ->willThrowException(new UserNotFoundException("id", $this->user->getId()));
        $this->conversationManager->expects(self::never())->method("createMessage");

        $this->client->request("POST", sprintf("/rest/users/%d/messages", $this->user->getId()),
            array ("content" => "Test message"));
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    private function createConversation(User $first, User $second) {
        $this->conversation = PrivateConversationMock::create(1, $first, $second);

        for ($i = 1; $i <= 25; $i++) {
            $author = ($i % 2 == 0) ? $second : $first;
            PrivateConversationMock::createMessage($i, $author, $this->conversation,
                "Message $i from " . $author->getUsername());
        }
    }
}