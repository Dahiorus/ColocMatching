<?php

namespace App\Tests\Core\Manager\Message;

use App\Core\DTO\AbstractDto;
use App\Core\DTO\Message\PrivateConversationDto;
use App\Core\DTO\Message\PrivateMessageDto;
use App\Core\DTO\User\UserDto;
use App\Core\Entity\Message\PrivateConversation;
use App\Core\Entity\User\UserStatus;
use App\Core\Exception\InvalidRecipientException;
use App\Core\Manager\Message\PrivateConversationDtoManager;
use App\Core\Manager\Message\PrivateConversationDtoManagerInterface;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Core\Mapper\Message\PrivateConversationDtoMapper;
use App\Core\Mapper\Message\PrivateMessageDtoMapper;
use App\Core\Repository\Filter\Pageable\PageRequest;
use App\Tests\AbstractServiceTest;
use App\Tests\CreateUserTrait;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;

class PrivateConversationDtoManagerTest extends AbstractServiceTest
{
    use CreateUserTrait;

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
        $this->em->close();

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
     *
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
        $this->firstParticipant = $this->createSearchUser($this->userManager, "first@yopmail.com");
        $this->userManager->updateStatus($this->firstParticipant, UserStatus::ENABLED);
        self::assertNotNull($this->firstParticipant, "Expected first participant to be created");
        self::assertNotEmpty($this->firstParticipant->getId(), "Expected first participant to have an ID");

        $this->secondParticipant = $this->createProposalUser($this->userManager, "second@yopmail.com");
        $this->userManager->updateStatus($this->secondParticipant, UserStatus::ENABLED);
        self::assertNotNull($this->secondParticipant, "Expected second participant to be created");
        self::assertNotEmpty($this->secondParticipant->getId(), "Expected second participant to have an ID");
    }


    /**
     * @throws \Exception
     */
    public function testFindAllConversationOfOneParticipant()
    {
        $conversations = $this->manager->findAll($this->secondParticipant, new PageRequest())->getContent();

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

        self::assertNotEmpty($messages->getContent(), "Expected to find messages between the two participants");
    }


    /**
     * @throws \Exception
     */
    public function testListMessagesBetweenOneParticipantAndSelf()
    {
        $messages = $this->manager->listMessages($this->secondParticipant, $this->secondParticipant, new PageRequest());

        self::assertEmpty($messages->getContent(), "Expected to find no message");
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
        $this->userManager->updateStatus($this->secondParticipant, UserStatus::BANNED);

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


    /**
     * @throws \Exception
     */
    public function testDelete()
    {
        $conversation = $this->manager->findOne($this->secondParticipant, $this->firstParticipant);
        $this->manager->delete($conversation);

        $conversation = $this->manager->findOne($this->firstParticipant, $this->secondParticipant);
        self::assertNull($conversation, "Expected not to find a private conversation");
    }


    public function testDeleteAll()
    {
        $this->manager->deleteAll();

        $entities = $this->em->getRepository(PrivateConversation::class)->findAll();
        self::assertEmpty($entities, "Expected to find no conversation");
    }

}
