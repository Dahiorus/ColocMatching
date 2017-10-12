<?php

namespace ColocMatching\CoreBundle\Tests\Manager\Message;

use ColocMatching\CoreBundle\Entity\User\PrivateConversation;
use ColocMatching\CoreBundle\Entity\User\PrivateMessage;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Exception\BadParameterException;
use ColocMatching\CoreBundle\Form\Type\Message\MessageType;
use ColocMatching\CoreBundle\Manager\Message\PrivateConversationManager;
use ColocMatching\CoreBundle\Manager\Message\PrivateConversationManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Repository\Message\PrivateConversationRepository;
use ColocMatching\CoreBundle\Tests\TestCase;
use ColocMatching\CoreBundle\Tests\Utils\Mock\Message\PrivateConversationMock;
use ColocMatching\CoreBundle\Tests\Utils\Mock\User\UserMock;
use ColocMatching\CoreBundle\Validator\EntityValidator;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;

class PrivateConversationManagerTest extends TestCase {

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var PrivateConversationManagerInterface
     */
    private $conversationManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $repository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $entityValidator;

    /**
     * @var User
     */
    private $firstParticipant;

    /**
     * @var User
     */
    private $secondParticipant;

    /**
     * @var PrivateConversation
     */
    private $conversation;


    protected function setUp() {
        $entityClass = "CoreBundle:Message\\PrivateConversation";
        $this->repository = $this->createMock(PrivateConversationRepository::class);
        $this->objectManager = $this->createMock(EntityManager::class);
        $this->objectManager->expects(self::once())->method("getRepository")->with($entityClass)
            ->willReturn($this->repository);
        $this->entityValidator = $this->createMock(EntityValidator::class);
        $this->logger = self::getContainer()->get("logger");

        $this->conversationManager = new PrivateConversationManager($this->objectManager, $entityClass,
            $this->entityValidator, $this->logger);

        $this->prepareTestData();
    }


    protected function tearDown() {
        $this->logger->info("Test end");
    }


    public function testFindAll() {
        $this->logger->info("Test finding all conversations of a user");

        $filter = new PageableFilter();

        $this->repository->expects(self::once())->method("findByParticipant")->with($filter,
            $this->firstParticipant)->willReturn(array ($this->conversation));

        $conversations = $this->conversationManager->findAll($this->firstParticipant, $filter);

        self::assertNotNull($conversations);
        self::assertEquals($conversations, array ($this->conversation));
    }


    public function testFindOne() {
        $this->logger->info("Test finding a conversation between 2 users");

        $this->repository->expects(self::once())->method("findOneByParticipants")->with($this->firstParticipant,
            $this->secondParticipant)->willReturn($this->conversation);

        $conversation = $this->conversationManager->findOne($this->firstParticipant, $this->secondParticipant);

        self::assertNotNull($conversation);
        self::assertEquals($this->conversation, $conversation);
    }


    public function testListMessages() {
        $this->logger->info("Test listing messages of a conversation");

        $filter = new PageableFilter();
        $filter->setSize(10);

        $this->repository->expects(self::once())->method("findOneByParticipants")->with($this->firstParticipant,
            $this->secondParticipant)->willReturn($this->conversation);

        $messages = $this->conversationManager->listMessages($this->firstParticipant, $this->secondParticipant,
            $filter);

        self::assertNotNull($messages);
        self::assertCount(10, $messages);
    }


    public function testListMessagesWithEmptyResponse() {
        $this->logger->info("Test listing messages of a non existing conversation");

        $filter = new PageableFilter();

        $this->repository->expects(self::once())->method("findOneByParticipants")->with($this->firstParticipant,
            $this->secondParticipant)->willReturn(null);

        $messages = $this->conversationManager->listMessages($this->firstParticipant, $this->secondParticipant,
            $filter);

        self::assertNotNull($messages);
        self::assertEmpty($messages);
    }


    public function testCreateMessageWithSuccess() {
        $this->logger->info("Test creating a new message with success");

        $author = $this->firstParticipant;
        $recipient = $this->secondParticipant;
        $msgCount = $this->conversation->getMessages()->count();
        $data = array ("content" => "Message from a test");
        $message = new PrivateMessage($author, $recipient);
        $message->setContent($data["content"]);

        $this->repository->expects(self::once())->method("findOneByParticipants")->with($author,
            $recipient)->willReturn($this->conversation);
        $this->entityValidator->expects(self::once())->method("validateEntityForm")->with(new PrivateMessage($author,
            $recipient), $data, MessageType::class, true)->willReturn($message);
        $this->objectManager->expects(self::once())->method("persist")->with($this->conversation);

        $conversation = $this->conversationManager->createMessage($author, $recipient, $data);

        self::assertNotNull($conversation);
        self::assertCount($msgCount + 1, $conversation->getMessages());
    }


    public function testCreateMessageWithBadParameter() {
        $this->logger->info("Test creating a message for the author");

        $author = $this->firstParticipant;

        $this->expectException(BadParameterException::class);
        $this->objectManager->expects(self::never())->method("persist");
        $this->entityValidator->expects(self::never())->method("validateEntityForm");

        $this->conversationManager->createMessage($author, $author, array ("Message from a failed test"));
    }


    public function testCreateMessageWithNoConversation() {
        $this->logger->info("Test creating a new message with no conversation");

        $author = $this->firstParticipant;
        $recipient = $this->secondParticipant;
        $data = array ("content" => "Message from a test");
        $message = new PrivateMessage($author, $recipient);
        $message->setContent($data["content"]);

        $this->repository->expects(self::once())->method("findOneByParticipants")->with($author,
            $recipient)->willReturn(null);
        $this->entityValidator->expects(self::once())->method("validateEntityForm")->with(new PrivateMessage($author,
            $recipient), $data, MessageType::class, true)->willReturn($message);
        $this->objectManager->expects(self::once())->method("persist")->with(self::isInstanceOf(PrivateConversation::class));

        $conversation = $this->conversationManager->createMessage($author, $recipient, $data);

        self::assertNotNull($conversation);
        self::assertEquals($author, $conversation->getFirstParticipant());
        self::assertEquals($recipient, $conversation->getSecondParticipant());
        self::assertCount(1, $conversation->getMessages());
    }


    private function prepareTestData() {
        $this->firstParticipant = UserMock::createUser(1, "first@test.fr", "password", "First", "Participant",
            UserConstants::TYPE_SEARCH);
        $this->secondParticipant = UserMock::createUser(2, "second@test.fr", "password", "Second", "Participant",
            UserConstants::TYPE_PROPOSAL);
        $this->conversation = PrivateConversationMock::create(1, $this->firstParticipant, $this->secondParticipant);

        for ($i = 1; $i <= 27; $i++) {
            $author = ($i % 2 == 0) ? $this->secondParticipant : $this->firstParticipant;
            PrivateConversationMock::createMessage($i, $author, $this->conversation, "Message $i.");
        }
    }

}