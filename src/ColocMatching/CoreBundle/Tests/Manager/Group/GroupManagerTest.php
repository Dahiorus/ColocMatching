<?php

namespace ColocMatching\CoreBundle\Tests\Manager\Group;

use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\Group\GroupPicture;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Exception\GroupNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Form\Type\Group\GroupType;
use ColocMatching\CoreBundle\Manager\Group\GroupManager;
use ColocMatching\CoreBundle\Manager\Group\GroupManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\GroupFilter;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Repository\Group\GroupRepository;
use ColocMatching\CoreBundle\Tests\TestCase;
use ColocMatching\CoreBundle\Tests\Utils\Mock\Group\GroupMock;
use ColocMatching\CoreBundle\Tests\Utils\Mock\Group\GroupPictureMock;
use ColocMatching\CoreBundle\Tests\Utils\Mock\User\UserMock;
use ColocMatching\CoreBundle\Validator\EntityValidator;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class GroupManagerTest extends TestCase {

    /**
     * @var GroupManagerInterface
     */
    private $groupManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $groupRepository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $entityValidator;

    /**
     * @var LoggerInterface
     */
    private $logger;


    protected function setUp() {
        $entityClass = "CoreBundle:Group\\Group";
        $this->groupRepository = $this->createMock(GroupRepository::class);
        $this->objectManager = $this->createMock(EntityManager::class);
        $this->entityValidator = $this->createMock(EntityValidator::class);
        $this->objectManager->expects($this->once())->method("getRepository")->with($entityClass)->willReturn(
            $this->groupRepository);
        $this->logger = self::getContainer()->get("logger");

        $this->groupManager = new GroupManager($this->objectManager, $entityClass, $this->entityValidator, $this->logger);
    }


    protected function tearDown() {
        $this->logger->info("Test end");
    }


    public function testList() {
        $this->logger->info("Test listing groups");

        $filter = new PageableFilter();
        $expectedGroups = GroupMock::createGroupPage($filter, 50);

        $this->groupRepository->expects($this->once())->method("findByPageable")->with($filter)->willReturn(
            $expectedGroups);

        $groups = $this->groupManager->list($filter);

        $this->assertNotNull($groups);
        $this->assertEquals($expectedGroups, $groups);
    }


    public function testCreateWithSuccess() {
        $this->logger->info("Test creating a new group with success");

        $user = UserMock::createUser(1, "user-test@test.fr", "password", "Toto", "Toto", UserConstants::TYPE_SEARCH);
        $data = array ("name" => "Group test", "description" => "Description of the group");
        $expectedGroup = GroupMock::createGroup(1, $user, $data["name"], $data["description"]);

        $this->entityValidator->expects($this->once())->method("validateEntityForm")->with(new Group($user), $data,
            GroupType::class, true)->willReturn($expectedGroup);
        $this->objectManager->expects($this->once())->method("persist")->with($expectedGroup);

        $group = $this->groupManager->create($user, $data);

        $this->assertNotNull($group);
        $this->assertEquals($expectedGroup, $group);
    }


    public function testCreateWithInvalidData() {
        $this->logger->info("Test creating a new group with invalid data");

        $user = UserMock::createUser(1, "user-test@test.fr", "password", "Toto", "Toto", UserConstants::TYPE_SEARCH);
        $data = array ("description" => "Description of the group");

        $this->entityValidator->expects($this->once())->method("validateEntityForm")->with(new Group($user), $data,
            GroupType::class, true)->willThrowException(
            new InvalidFormDataException("Exception from testCreateWithInvalidData()",
                self::getForm(GroupType::class)->getErrors()));
        $this->objectManager->expects($this->never())->method("persist");
        $this->expectException(InvalidFormDataException::class);

        $this->groupManager->create($user, $data);

        $this->assertNull($user->getGroup());
    }


    public function testCreateWithUnprocessableEntity() {
        $this->logger->info("Test creating a new group with unprocessable entity");

        $user = UserMock::createUser(1, "user@test.fr", "secret", "Toto", "Toto", UserConstants::TYPE_SEARCH);
        $user->setGroup(GroupMock::createGroup(1, $user, "Group test", "Description of group"));
        $data = array ("name" => "Group name", "description" => "Description of the group");

        $this->entityValidator->expects($this->never())->method("validateEntityForm");
        $this->objectManager->expects($this->never())->method("persist");
        $this->expectException(UnprocessableEntityHttpException::class);

        $this->groupManager->create($user, $data);
    }


    public function testReadWithSuccess() {
        $this->logger->info("Test reading an existing group with success");

        $expectedGroup = GroupMock::createGroup(1,
            UserMock::createUser(1, "user@test.fr", "secret", "Toto", "Toto", UserConstants::TYPE_SEARCH), "Group 1",
            "Description of group");

        $this->groupRepository->expects($this->once())->method("findById")->with(1)->willReturn($expectedGroup);

        $group = $this->groupManager->read(1);

        $this->assertNotNull($group);
        $this->assertEquals($expectedGroup, $group);
    }


    public function testSelectFieldsOfOneGroup() {
        $this->logger->info("Test selecting fields of an existing group");

        $fields = array ("id", "name");
        $expectedGroup = GroupMock::createGroup(1,
            UserMock::createUser(1, "user@test.fr", "secret", "Toto", "Toto", UserConstants::TYPE_SEARCH), "Group 1",
            "Description of group");

        $this->groupRepository->expects($this->once())->method("findById")->with(1, $fields)->willReturn(
            array ("id" => $expectedGroup->getId(), "name" => $expectedGroup->getName()));

        $group = $this->groupManager->read(1, $fields);

        $this->assertNotNull($group);
        $this->assertEquals($expectedGroup->getId(), $group["id"]);
        $this->assertArrayHasKey("name", $group);
        $this->assertArrayNotHasKey("description", $group);
    }


    public function testReadWithNotFound() {
        $this->logger->info("Test reading an existing group with not found exception");

        $this->groupRepository->expects($this->once())->method("findById")->with(1)->willReturn(null);
        $this->expectException(GroupNotFoundException::class);

        $this->groupManager->read(1);
    }


    public function testFullUpdateWithSuccess() {
        $this->logger->info("Test updating (full) an existing group with success");

        $id = 1;
        $user = UserMock::createUser(1, "user@test.fr", "secret", "Toto", "Toto", UserConstants::TYPE_SEARCH);
        $group = GroupMock::createGroup($id, $user, "Group name", "Group description");
        $data = array ("name" => "New name", "description" => $group->getDescription());
        $expectedGroup = GroupMock::createGroup($id, $user, $data["name"], $data["description"]);

        $this->entityValidator->expects($this->once())->method("validateEntityForm")->with($group, $data,
            GroupType::class, true)->willReturn($expectedGroup);
        $this->objectManager->expects($this->once())->method("persist")->with($expectedGroup);

        $updatedGroup = $this->groupManager->update($group, $data, true);

        $this->assertNotNull($updatedGroup);
        $this->assertEquals($expectedGroup, $updatedGroup);
    }


    public function testFullUpdateWithInvalidData() {
        $this->logger->info("Test updating (full) an existing group with invalid data");

        $data = array ("name" => null);
        $group = GroupMock::createGroup(1,
            UserMock::createUser(1, "user@test.fr", "secret", "Toto", "Toto", UserConstants::TYPE_SEARCH), "Group name",
            "Group description");

        $this->entityValidator->expects($this->once())->method("validateEntityForm")->with($group, $data,
            GroupType::class, true)->willThrowException(
            new InvalidFormDataException("Exception from testFullUpdateWithInvalidData()",
                self::getForm(GroupType::class)->getErrors()));
        $this->expectException(InvalidFormDataException::class);
        $this->objectManager->expects($this->never())->method("persist");

        $this->groupManager->update($group, $data, true);
    }


    public function testPartialUpdateWithSuccess() {
        $this->logger->info("Test updating (partial) an existing group with success");

        $id = 1;
        $user = UserMock::createUser(1, "user@test.fr", "secret", "Toto", "Toto", UserConstants::TYPE_SEARCH);
        $group = GroupMock::createGroup($id, $user, "Group name", "Group description");
        $data = array ("description" => "New description");
        $expectedGroup = GroupMock::createGroup($id, $user, $group->getName(), $data["description"]);

        $this->entityValidator->expects($this->once())->method("validateEntityForm")->with($group, $data,
            GroupType::class, false)->willReturn($expectedGroup);
        $this->objectManager->expects($this->once())->method("persist")->with($expectedGroup);

        $updatedGroup = $this->groupManager->update($group, $data, false);

        $this->assertNotNull($updatedGroup);
        $this->assertEquals($expectedGroup, $updatedGroup);
    }


    public function testPartialUpdateWithInvalidData() {
        $this->logger->info("Test updating (partial) an existing group with invalid data");

        $data = array ("name" => null);
        $group = GroupMock::createGroup(1,
            UserMock::createUser(1, "user@test.fr", "secret", "Toto", "Toto", UserConstants::TYPE_SEARCH), "Group name",
            "Group description");

        $this->entityValidator->expects($this->once())->method("validateEntityForm")->with($group, $data,
            GroupType::class, false)->willThrowException(
            new InvalidFormDataException("Exception from testPartialUpdateWithInvalidData()",
                self::getForm(GroupType::class)->getErrors()));
        $this->expectException(InvalidFormDataException::class);
        $this->objectManager->expects($this->never())->method("persist");

        $this->groupManager->update($group, $data, false);
    }


    public function testDelete() {
        $this->logger->info("Test deleteing an exisiting group");

        $group = GroupMock::createGroup(1,
            UserMock::createUser(1, "user@test.fr", "secret", "Toto", "Toto", UserConstants::TYPE_SEARCH), "Group name",
            "Group description");

        $this->objectManager->expects($this->once())->method("remove")->with($group);

        $this->groupManager->delete($group);
    }


    public function testSearch() {
        $this->logger->info("Test searching groups");

        $filter = new GroupFilter();
        $expectedGroups = GroupMock::createGroupPage($filter, 50);

        $this->groupRepository->expects($this->once())->method("findByFilter")->with($filter)->willReturn(
            $expectedGroups);

        $groups = $this->groupManager->search($filter);

        $this->assertNotNull($groups);
        $this->assertEquals($expectedGroups, $groups);
    }


    public function testAddMemberWihSuccess() {
        $this->logger->info("Test adding a new member to a group with success");

        $user = UserMock::createUser(1, "user@test.fr", "secret", "Toto", "Toto", UserConstants::TYPE_SEARCH);
        $group = GroupMock::createGroup(1,
            UserMock::createUser(2, "creator@test.fr", "secret", "Titi", "Titi", UserConstants::TYPE_SEARCH),
            "Group test", "Description of group");

        $this->objectManager->expects($this->once())->method("persist")->with($group);

        $members = $this->groupManager->addMember($group, $user);

        $this->assertCount(2, $members);
    }


    public function testAddMemberWithUnprocessableEntity() {
        $this->logger->info("Test adding a new member to a group with unprocessable entity");

        $user = UserMock::createUser(1, "user@test.fr", "secret", "Toto", "Toto", UserConstants::TYPE_PROPOSAL);
        $group = GroupMock::createGroup(1,
            UserMock::createUser(2, "creator@test.fr", "secret", "Titi", "Titi", UserConstants::TYPE_SEARCH),
            "Group test", "Description of group");

        $this->expectException(UnprocessableEntityHttpException::class);
        $this->objectManager->expects($this->never())->method("persist");

        $this->groupManager->addMember($group, $user);
    }


    public function testRemoveMemberWithSuccess() {
        $this->logger->info("Test removing a member from a group with success");

        $user = UserMock::createUser(1, "user@test.fr", "secret", "Toto", "Toto", UserConstants::TYPE_SEARCH);
        $group = GroupMock::createGroup(1,
            UserMock::createUser(2, "creator@test.fr", "secret", "Titi", "Titi", UserConstants::TYPE_SEARCH),
            "Group test", "Description of group");
        $group->addMember($user);

        $this->objectManager->expects($this->once())->method("persist")->with($group);

        $this->groupManager->removeMember($group, $user->getId());

        $this->assertCount(1, $group->getMembers());
    }


    public function testRemoveMemberNotFound() {
        $this->logger->info("Test removing an non exisiting member from a group");

        $group = GroupMock::createGroup(1,
            UserMock::createUser(2, "creator@test.fr", "secret", "Titi", "Titi", UserConstants::TYPE_SEARCH),
            "Group test", "Description of group");

        $this->objectManager->expects($this->never())->method("persist");

        $this->groupManager->removeMember($group, 1);

        $this->assertCount(1, $group->getMembers());
    }


    public function testRemoveCreator() {
        $this->logger->info("Test removing the creator of a group");

        $group = GroupMock::createGroup(1,
            UserMock::createUser(2, "creator@test.fr", "secret", "Titi", "Titi", UserConstants::TYPE_SEARCH),
            "Group test", "Description of group");

        $this->expectException(UnprocessableEntityHttpException::class);
        $this->objectManager->expects($this->never())->method("persist");

        $this->groupManager->removeMember($group, $group->getCreator()->getId());
    }


    public function testUploadGroupPicture() {
        $this->logger->info("Test uploading a picture for a group");

        $group = GroupMock::createGroup(1,
            UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_SEARCH), "Group",
            "Upload picture test group");
        $file = $this->createTempFile(dirname(__FILE__) . "/../../Resources/uploads/image.jpg", "group-img.jpg");
        $expectedPicture = GroupPictureMock::createPicture(1, $file, "picture-test.jpg");

        $this->entityValidator->expects($this->once())->method("validateDocumentForm")->with(new GroupPicture(), $file,
            GroupPicture::class)->willReturn($expectedPicture);
        $this->objectManager->expects($this->once())->method("persist")->with($group);

        $picture = $this->groupManager->uploadGroupPicture($group, $file);

        $this->assertNotNull($picture);
        $this->assertEquals($expectedPicture, $picture);
    }


    public function testDeleteGroupPictureWithSuccess() {
        $this->logger->info("Test deleting a picture of a group with success");

        $group = GroupMock::createGroup(1,
            UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_SEARCH), "Group",
            "Upload picture test group");
        $file = $this->createTempFile(dirname(__FILE__) . "/../../Resources/uploads/image.jpg", "group-img.jpg");
        $group->setPicture(GroupPictureMock::createPicture(1, $file, "picture-test.jpg"));

        $this->objectManager->expects($this->once())->method("remove")->with($group->getPicture());

        $this->groupManager->deleteGroupPicture($group);

        $this->assertNull($group->getPicture());
    }


    public function testDeleteGroupPictureWithFailure() {
        $this->logger->info("Test deleting a picture of a group with failure");

        $group = GroupMock::createGroup(1,
            UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_SEARCH), "Group",
            "Upload picture test group");

        $this->objectManager->expects($this->never())->method("remove");

        $this->groupManager->deleteGroupPicture($group);
    }

}