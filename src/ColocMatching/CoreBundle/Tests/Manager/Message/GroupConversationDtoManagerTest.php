<?php

namespace ColocMatching\CoreBundle\Tests\Manager\Message;

use ColocMatching\CoreBundle\DTO\AbstractDto;
use ColocMatching\CoreBundle\DTO\Group\GroupDto;
use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\Message\GroupConversation;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Exception\InvalidParameterException;
use ColocMatching\CoreBundle\Manager\Group\GroupDtoManagerInterface;
use ColocMatching\CoreBundle\Manager\Message\GroupConversationDtoManager;
use ColocMatching\CoreBundle\Manager\Message\GroupConversationDtoManagerInterface;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
use ColocMatching\CoreBundle\Mapper\Group\GroupDtoMapper;
use ColocMatching\CoreBundle\Mapper\Message\GroupConversationDtoMapper;
use ColocMatching\CoreBundle\Mapper\Message\GroupMessageDtoMapper;
use ColocMatching\CoreBundle\Mapper\User\UserDtoMapper;
use ColocMatching\CoreBundle\Tests\AbstractServiceTest;
use ColocMatching\CoreBundle\Validator\FormValidator;
use Doctrine\ORM\EntityManagerInterface;

class GroupConversationDtoManagerTest extends AbstractServiceTest
{
    /** @var GroupConversationDtoManagerInterface */
    private $manager;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var GroupDto */
    private $group;

    /** @var UserDto */
    private $member;

    /** @var UserDto */
    private $creator;


    /**
     * @throws \Exception
     */
    protected function setUp()
    {
        parent::setUp();

        $this->manager = $this->initManager();

        $this->cleanData();
        $this->createAndAssertEntity();
    }


    /**
     * @throws \Exception
     */
    protected function tearDown()
    {
        $this->cleanData();
        parent::tearDown();
    }


    /**
     * Initiates the CRUD manager
     * @return GroupConversationDtoManagerInterface An instance of the manager
     */
    protected function initManager()
    {
        $this->entityManager = $this->getService("doctrine.orm.entity_manager");
        /** @var GroupMessageDtoMapper $messageDtoMapper */
        $messageDtoMapper = $this->getService("coloc_matching.core.group_message_dto_mapper");
        /** @var GroupConversationDtoMapper $conversationDtoMapper */
        $conversationDtoMapper = $this->getService("coloc_matching.core.group_conversation_dto_mapper");
        /** @var FormValidator $formValidator */
        $formValidator = $this->getService("coloc_matching.core.form_validator");

        return new GroupConversationDtoManager($this->logger, $this->entityManager, $formValidator,
            $conversationDtoMapper, $messageDtoMapper);
    }


    /**
     * @throws \Exception
     */
    protected function createAndAssertEntity()
    {
        $this->createGroupAndMembers();
        $data = array ("content" => "Hello there!");

        $message = $this->manager->createMessage($this->member, $this->group, $data);

        $this->assertDto($message);
        self::assertEquals($data["content"], $message->getContent());
        self::assertEquals($this->member->getId(), $message->getAuthorId());
        self::assertEquals($this->group->getId(), $message->getGroupId());

        /** @var GroupConversation $conversation */
        $conversation = $this->entityManager->find(GroupConversation::class, $message->getConversationId());
        self::assertNotEmpty($conversation, "Expected group conversation to be created");
        self::assertNotEmpty($conversation->getMessages(), "Expected conversation to have messages");
    }


    /**
     * @throws \Exception
     */
    protected function cleanData()
    {
        $this->manager->deleteAll();

        /** @var GroupDtoManagerInterface $groupManager */
        $groupManager = $this->getService("coloc_matching.core.group_dto_manager");
        $groupManager->deleteAll();

        /** @var UserDtoManagerInterface $userManager */
        $userManager = $this->getService("coloc_matching.core.user_dto_manager");
        $userManager->deleteAll();
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


    private function createGroupAndMembers()
    {
        $creator = new User("creator@test.fr", "password", "Creator", "Test");
        $creator->setStatus(UserConstants::STATUS_ENABLED);
        $this->entityManager->persist($creator);

        $member = new User("member@test.fr", "password", "Member", "Test");
        $member->setStatus(UserConstants::STATUS_ENABLED);
        $this->entityManager->persist($member);

        $this->entityManager->flush();

        $group = new Group($creator);
        $group->setName("Group test");
        $group->addMember($member);

        $this->entityManager->persist($group);
        $this->entityManager->flush();

        /** @var UserDtoMapper $userDtoMapper */
        $userDtoMapper = $this->getService("coloc_matching.core.user_dto_mapper");
        $this->creator = $userDtoMapper->toDto($creator);
        $this->member = $userDtoMapper->toDto($member);

        /** @var GroupDtoMapper $groupDtoMapper */
        $groupDtoMapper = $this->getService("coloc_matching.core.group_dto_mapper");
        $this->group = $groupDtoMapper->toDto($group);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function listGroupConversationMessages()
    {
        $messages = $this->manager->listMessages($this->group);

        self::assertNotEmpty($messages, "Expected to get the group messages");
    }


    /**
     * @test
     * @throws \Exception
     */
    public function createMessageToExistingConversation()
    {
        $message = $this->manager->createMessage($this->creator, $this->group, array ("content" => "Hello!"));

        $this->assertDto($message);
        self::assertEquals($this->creator->getId(), $message->getAuthorId(),
            "Expected the creator to be the message author");
        self::assertEquals($this->group->getId(), $message->getGroupId(),
            "Expected the message group to be the specified one");
    }


    /**
     * @test
     * @throws \Exception
     */
    public function createMessageToNonAvailableGroupShouldThrowInvalidParameter()
    {
        /** @var GroupDtoManagerInterface $groupManager */
        $groupManager = $this->getService("coloc_matching.core.group_dto_manager");
        $this->group = $groupManager->update($this->group, array ("status" => Group::STATUS_CLOSED), false);

        $this->expectException(InvalidParameterException::class);

        $this->manager->createMessage($this->creator, $this->group, array ("content" => "Hello!"));
    }


    /**
     * @test
     * @throws \Exception
     */
    public function createMessageAsNonMemberShouldThrowInvalidParameter()
    {
        /** @var UserDtoManagerInterface $userManager */
        $userManager = $this->getService("coloc_matching.core.user_dto_manager");
        $user = $userManager->create(array (
            "email" => "non-member@test.fr",
            "plainPassword" => "password",
            "firstName" => "User",
            "lastName" => "Test",
            "type" => "search"
        ));

        $this->expectException(InvalidParameterException::class);

        $this->manager->createMessage($user, $this->group, array ("content" => "Hello!"));
    }


    /**
     * @test
     * @throws \Exception
     */
    public function createMessageWithInvalidDataShouldThrowInvalidForm()
    {
        $this->expectException(InvalidFormException::class);

        $this->manager->createMessage($this->creator, $this->group, array ("otherProperty" => "Hello!"));
    }

}
