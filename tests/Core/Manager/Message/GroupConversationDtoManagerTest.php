<?php

namespace App\Tests\Core\Manager\Message;

use App\Core\DTO\AbstractDto;
use App\Core\DTO\Collection;
use App\Core\DTO\Group\GroupDto;
use App\Core\DTO\Page;
use App\Core\DTO\User\UserDto;
use App\Core\Entity\Group\Group;
use App\Core\Entity\Message\GroupConversation;
use App\Core\Entity\User\User;
use App\Core\Entity\User\UserStatus;
use App\Core\Exception\InvalidFormException;
use App\Core\Exception\InvalidParameterException;
use App\Core\Manager\Group\GroupDtoManagerInterface;
use App\Core\Manager\Message\GroupConversationDtoManager;
use App\Core\Manager\Message\GroupConversationDtoManagerInterface;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Core\Mapper\Group\GroupDtoMapper;
use App\Core\Mapper\Message\GroupMessageDtoMapper;
use App\Core\Mapper\User\UserDtoMapper;
use App\Core\Repository\Filter\Pageable\PageRequest;
use App\Core\Validator\FormValidator;
use App\Tests\AbstractServiceTest;
use App\Tests\CreateUserTrait;
use Doctrine\ORM\EntityManagerInterface;

class GroupConversationDtoManagerTest extends AbstractServiceTest
{
    use CreateUserTrait;

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
        /** @var FormValidator $formValidator */
        $formValidator = $this->getService("coloc_matching.core.form_validator");

        return new GroupConversationDtoManager($this->logger, $this->entityManager, $formValidator, $messageDtoMapper);
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
        $creator->setStatus(UserStatus::ENABLED);
        $this->entityManager->persist($creator);

        $member = new User("member@test.fr", "password", "Member", "Test");
        $member->setStatus(UserStatus::ENABLED);
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
        self::assertInstanceOf(Collection::class, $messages, "Expected to have a Collection instance");
    }


    /**
     * @test
     * @throws \Exception
     */
    public function listGroupConversationMessagesWithPaging()
    {
        $messages = $this->manager->listMessages($this->group, new PageRequest());

        self::assertNotEmpty($messages, "Expected to get the group messages");
        self::assertInstanceOf(Page::class, $messages, "Expected to have a Page instance");
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
        $user = $this->createSearchUser($userManager, "non-member@test.fr");

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
