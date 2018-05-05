<?php

namespace ColocMatching\CoreBundle\Tests\Manager\Message;

use ColocMatching\CoreBundle\DTO\AbstractDto;
use ColocMatching\CoreBundle\DTO\Message\PrivateConversationDto;
use ColocMatching\CoreBundle\DTO\Message\PrivateMessageDto;
use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Exception\InvalidRecipientException;
use ColocMatching\CoreBundle\Manager\Message\PrivateConversationDtoManager;
use ColocMatching\CoreBundle\Manager\Message\PrivateConversationDtoManagerInterface;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
use ColocMatching\CoreBundle\Mapper\Message\PrivateConversationDtoMapper;
use ColocMatching\CoreBundle\Mapper\Message\PrivateMessageDtoMapper;
use ColocMatching\CoreBundle\Repository\Filter\Pageable\PageRequest;
use ColocMatching\CoreBundle\Tests\AbstractServiceTest;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;

class PrivateConversationDtoManagerTest extends AbstractServiceTest
{
    /** @var EntityManagerInterface */
    protected $em;

    /** @var PrivateConversationDtoMapper */
    protected $conversationDtoMapper;

    /** @var PrivateMessageDtoMapper */
    protected $messageDtoMapper;

    /** @var PrivateConversationDtoManagerInterface */
    protected $manager;

    /** @var UserDtoManagerInterface */
    protected $userManager;

    /** @var UserDto */
    private $firstParticipant;

    /** @var UserDto */
    private $secondParticipant;


    /**
     * @throws \Exception
     */
    protected function setUp()
    {
        parent::setUp();

        $this->em = $this->getService("doctrine.orm.entity_manager");
        $this->manager = $this->initManager();

        $this->cleanData();
        $this->createAndAssertEntity();
    }


    protected function tearDown()
    {
        $this->cleanData();
        parent::tearDown();
    }


    /**
     * Gets the entity repository of an entity class
     *
     * @param string $entityClass The string representation of the entity class
     *
     * @return ObjectRepository The entity class repository
     */
    protected function getRepository(string $entityClass)
    {
        return $this->em->getRepository($entityClass);
    }


    /**
     * Initiates the CRUD manager
     * @return PrivateConversationDtoManagerInterface An instance of the manager
     */
    protected function initManager()
    {
        $this->userManager = $this->getService("coloc_matching.core.user_dto_manager");

        $this->conversationDtoMapper = $this->getService("coloc_matching.core.private_conversation_dto_mapper");
        $this->messageDtoMapper = $this->getService("coloc_matching.core.private_message_dto_mapper");

        $entityValidator = $this->getService("coloc_matching.core.form_validator");
        $userDtoMapper = $this->getService("coloc_matching.core.user_dto_mapper");

        return new PrivateConversationDtoManager($this->logger, $this->em, $this->conversationDtoMapper,
            $this->messageDtoMapper, $entityValidator, $userDtoMapper);
    }


    /**
     * Cleans all test data
     */
    protected function cleanData() : void
    {
        $purger = new ORMPurger($this->em);
        $purger->purge();
    }


    /**
     * @throws \Exception
     */
    protected function createAndAssertEntity()
    {
        $this->createParticipant();
        $data = array ("content" => "Hello, nice to meet you!");

        /** @var PrivateMessageDto $message */
        $message = $this->manager->createMessage($this->firstParticipant, $this->secondParticipant, $data);

        $this->assertDto($message);
        self::assertEquals($data["content"], $message->getContent());
        self::assertEquals($this->firstParticipant->getId(), $message->getAuthorId());
        self::assertEquals($this->secondParticipant->getId(), $message->getRecipientId());

        /** @var PrivateConversationDto $conversation */
        $conversation = $this->manager->findOne($this->secondParticipant, $this->firstParticipant);

        $this->assertDto($conversation);
        self::assertNotEmpty($conversation->getMessages(), "Expected conversation to have messages");

        self::assertEquals($message->getConversationId(), $conversation->getId(),
            "Expected the message to be in the conversation");
    }


    /**
     * Asserts the entity data (can be overrode to assert other properties)
     *
     * @param AbstractDto $dto
     */
    protected function assertDto($dto) : void
    {
        self::assertNotNull($dto, "Expected DTO to be not null");
        self::assertNotEmpty($dto->getId(), "Expected DTO to have an identifier");
    }


    /**
     * @throws \Exception
     */
    private function createParticipant()
    {
        $this->firstParticipant = $this->userManager->create(array (
            "email" => "first@yopmail.com",
            "plainPassword" => "secret123",
            "firstName" => "First",
            "lastName" => "Participant",
            "type" => UserConstants::TYPE_SEARCH));
        $this->userManager->updateStatus($this->firstParticipant, UserConstants::STATUS_ENABLED);
        self::assertNotNull($this->firstParticipant, "Expected first participant to be created");
        self::assertNotEmpty($this->firstParticipant->getId(), "Expected first participant to have an ID");

        $this->secondParticipant = $this->userManager->create(array (
            "email" => "second@yopmail.com",
            "plainPassword" => "secret123",
            "firstName" => "Second",
            "lastName" => "Participant",
            "type" => UserConstants::TYPE_PROPOSAL));
        $this->userManager->updateStatus($this->secondParticipant, UserConstants::STATUS_ENABLED);
        self::assertNotNull($this->secondParticipant, "Expected second participant to be created");
        self::assertNotEmpty($this->secondParticipant->getId(), "Expected second participant to have an ID");
    }


    public function testFindAllConversationOfOneParticipant()
    {
        $conversations = $this->manager->findAll($this->secondParticipant, new PageRequest());

        self::assertNotEmpty($conversations, "Expected to find conversation of the second participant");

        array_walk($conversations, function (PrivateConversationDto $c) {
            self::assertEquals($this->secondParticipant->getId(), $c->getSecondParticipantId(),
                "Expected the second participant to be in the conversation");
        });
    }


    /**
     * @throws \Exception
     */
    public function testFindOneConversationBetweenOneParticipantAndSelf()
    {
        $conversation = $this->manager->findOne($this->secondParticipant, $this->secondParticipant);

        self::assertEmpty($conversation, "Expected to find no conversation");
    }


    /**
     * @throws \Exception
     */
    public function testListMessagesBetweenTwoParticipants()
    {
        $messages = $this->manager->listMessages($this->firstParticipant, $this->secondParticipant, new PageRequest());

        self::assertNotEmpty($messages, "Expected to find messages between the two participants");
    }


    /**
     * @throws \Exception
     */
    public function testListMessagesBetweenOneParticipantAndSelf()
    {
        $messages = $this->manager->listMessages($this->secondParticipant, $this->secondParticipant, new PageRequest());

        self::assertEmpty($messages, "Expected to find no message");
    }


    /**
     * @throws \Exception
     */
    public function testCreateMessageToSelfShouldThrowInvalidRecipient()
    {
        $this->expectException(InvalidRecipientException::class);

        $this->manager->createMessage($this->firstParticipant, $this->firstParticipant, array ("content" => "Hello!"));
    }


    /**
     * @throws \Exception
     */
    public function testCreateMessageToBannedUserShouldThrowInvalidRecipient()
    {
        $this->userManager->updateStatus($this->secondParticipant, UserConstants::STATUS_BANNED);

        $this->expectException(InvalidRecipientException::class);

        $this->manager->createMessage($this->firstParticipant, $this->secondParticipant, array ("content" => "Hello!"));
    }


    /**
     * @throws \Exception
     */
    public function testCreateMessageToExistingConversation()
    {
        $data = array ("content" => "Hello, this is a response");

        /** @var PrivateMessageDto $message */
        $message = $this->manager->createMessage($this->secondParticipant, $this->firstParticipant, $data);

        $this->assertDto($message);
        self::assertEquals($data["content"], $message->getContent());
        self::assertEquals($this->secondParticipant->getId(), $message->getAuthorId());
        self::assertEquals($this->firstParticipant->getId(), $message->getRecipientId());
        self::assertNotNull($message->getParentId(), "Expected message to have a parent");
    }
}