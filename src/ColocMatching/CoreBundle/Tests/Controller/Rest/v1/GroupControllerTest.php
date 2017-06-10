<?php

namespace ColocMatching\CoreBundle\Tests\Controller\Rest\v1;

use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Exception\GroupNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Form\Type\Group\GroupType;
use ColocMatching\CoreBundle\Manager\Group\GroupManager;
use ColocMatching\CoreBundle\Repository\Filter\GroupFilter;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Tests\Controller\Rest\v1\RestTestCase;
use ColocMatching\CoreBundle\Tests\Utils\Mock\Group\GroupMock;
use ColocMatching\CoreBundle\Tests\Utils\Mock\Group\GroupPictureMock;
use ColocMatching\CoreBundle\Tests\Utils\Mock\User\UserMock;
use Doctrine\Common\Collections\ArrayCollection;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class GroupControllerTest extends RestTestCase {

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $groupManager;

    /**
     * @var LoggerInterface
     */
    private $logger;


    protected function setUp() {
        parent::setUp();

        $this->groupManager = self::createMock(GroupManager::class);
        $this->client->getContainer()->set("coloc_matching.core.group_manager", $this->groupManager);
        $this->logger = $this->client->getContainer()->get("logger");
    }


    public function testGetGroupsActionWith200() {
        $this->logger->info("Test getting groups with status code 200");

        $total = 30;
        $filter = new PageableFilter();
        $filter->setPage(2);
        $groups = GroupMock::createGroupPage($filter, $total);

        $this->groupManager->expects($this->once())->method("list")->with($filter)->willReturn($groups);
        $this->groupManager->expects($this->once())->method("countAll")->willReturn($total);

        $this->client->request("GET", "/rest/groups", array ("page" => $filter->getPage()));
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_OK, $response["code"]);
        $this->assertEquals(count($groups), count($response["rest"]["content"]));
        $this->assertEquals($filter->getSize(), $response["rest"]["size"]);
    }


    public function testGetGroupsActionWith206() {
        $this->logger->info("Test getting groups with status code 206");

        $total = 50;
        $filter = new PageableFilter();
        $groups = GroupMock::createGroupPage($filter, $total);

        $this->groupManager->expects($this->once())->method("list")->with($filter)->willReturn($groups);
        $this->groupManager->expects($this->once())->method("countAll")->willReturn($total);

        $this->client->request("GET", "/rest/groups");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_PARTIAL_CONTENT, $response["code"]);
        $this->assertEquals(count($groups), count($response["rest"]["content"]));
    }


    public function testCreateGroupActionWith201() {
        $this->logger->info("Test creating a group with success");

        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_SEARCH);
        $data = array ("name" => "Created group");
        $expectedGroup = GroupMock::createGroup(1, $user, $data["name"], null);

        $this->groupManager->expects($this->once())->method("create")->with($user, $data)->willReturn($expectedGroup);

        $this->setAuthenticatedRequest($user);
        $this->client->request("POST", "/rest/groups", $data);
        $response = $this->getResponseContent();
        $group = $response["rest"]["content"];

        $this->assertEquals(Response::HTTP_CREATED, $response["code"]);
        $this->assertEquals($expectedGroup->getId(), $group["id"]);
        $this->assertEquals($expectedGroup->getName(), $group["name"]);
    }


    public function testCreateGroupActionWith403() {
        $this->logger->info("Test creating a group with a PROPOSAL user");

        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);
        $data = array ("name" => "Group test", "budget" => 250);

        $this->groupManager->expects($this->never())->method("create");

        $this->setAuthenticatedRequest($user);
        $this->client->request("POST", "/rest/groups", $data);
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_FORBIDDEN, $response["code"]);
    }


    public function testCreateGroupActionWith400() {
        $this->logger->info("Test creating a group with a Bad request");

        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_SEARCH);
        $data = array ("name" => "", "budget" => 250);

        $this->groupManager->expects($this->once())->method("create")->with($user, $data)->willThrowException(
            new InvalidFormDataException("Exception from testCreateGroupWith400",
                $this->getForm(GroupType::class)->getErrors()));

        $this->setAuthenticatedRequest($user);
        $this->client->request("POST", "/rest/groups", $data);
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response["code"]);
    }


    public function testCreateGroupActionWith422() {
        $this->logger->info("Test creating a group with an unprocessable entity");

        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_SEARCH);
        $user->setGroup(GroupMock::createGroup(1, $user, "Group", null));
        $data = array ("name" => "New group", "budget" => 250);

        $this->groupManager->expects($this->once())->method("create")->with($user, $data)->willThrowException(
            new UnprocessableEntityHttpException());

        $this->setAuthenticatedRequest($user);
        $this->client->request("POST", "/rest/groups", $data);
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response["code"]);
    }


    public function testGetGroupActionWith200() {
        $this->logger->info("Test getting a group with success");

        $id = 1;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_SEARCH);
        $expectedGroup = GroupMock::createGroup($id, $user, "Expected group", "Description");

        $this->groupManager->expects($this->once())->method("read")->with($id)->willReturn($expectedGroup);

        $this->setAuthenticatedRequest($user);
        $this->client->request("GET", "/rest/groups/$id");
        $response = $this->getResponseContent();
        $group = $response["rest"]["content"];

        $this->assertEquals(Response::HTTP_OK, $response["code"]);
        $this->assertEquals($expectedGroup->getId(), $group["id"]);
    }


    public function testGetGroupActionWith404() {
        $this->logger->info("Test getting a group with not found exception");

        $id = 1;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_SEARCH);

        $this->groupManager->expects($this->once())->method("read")->with($id)->willThrowException(
            new GroupNotFoundException("id", $id));

        $this->setAuthenticatedRequest($user);
        $this->client->request("GET", "/rest/groups/$id");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testUpdateGroupActionWith200() {
        $this->logger->info("Test updating a group with success");

        $id = 2;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_SEARCH);
        $group = GroupMock::createGroup($id, $user, "Group", "Group to update");
        $data = array ("name" => "New name", "description" => $group->getDescription(), "budget" => 942);
        $expectedGroup = GroupMock::createGroup($group->getId(), $group->getCreator(), $data["name"],
            $data["description"]);
        $expectedGroup->setBudget($data["budget"]);

        $this->groupManager->expects($this->once())->method("read")->with($id)->willReturn($group);
        $this->groupManager->expects($this->once())->method("update")->with($group, $data, true)->willReturn(
            $expectedGroup);

        $this->setAuthenticatedRequest($user);
        $this->client->request("PUT", "/rest/groups/$id", $data);
        $response = $this->getResponseContent();
        $updatedGroup = $response["rest"]["content"];

        $this->assertEquals(Response::HTTP_OK, $response["code"]);
        $this->assertEquals($expectedGroup->getId(), $updatedGroup["id"]);
        $this->assertEquals($expectedGroup->getName(), $updatedGroup["name"]);
        $this->assertEquals($expectedGroup->getBudget(), $updatedGroup["budget"]);
    }


    public function testUpdateGroupActionWith404() {
        $this->logger->info("Test updating a non existing group");

        $id = 2;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_SEARCH);
        $data = array ("name" => "New name", "description" => null, "budget" => 942);

        $this->groupManager->expects($this->once())->method("read")->willThrowException(
            new GroupNotFoundException("id", $id));
        $this->groupManager->expects($this->never())->method("update");

        $this->setAuthenticatedRequest($user);
        $this->client->request("PUT", "/rest/groups/$id", $data);
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testUpdateGroupActionWith400() {
        $this->logger->info("Test updating a group with bad request");

        $id = 2;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_SEARCH);
        $group = GroupMock::createGroup($id, $user, "Group", "Group to update");
        $data = array ("description" => $group->getDescription());

        $this->groupManager->expects($this->once())->method("read")->with($id)->willReturn($group);
        $this->groupManager->expects($this->once())->method("update")->with($group, $data, true)->willThrowException(
            new InvalidFormDataException("Exception from testUpdateGroupWith400",
                $this->getForm(GroupType::class)->getErrors()));

        $this->setAuthenticatedRequest($user);
        $this->client->request("PUT", "/rest/groups/$id", $data);
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response["code"]);
    }


    public function testDeleteGroupActionWithSuccess() {
        $this->logger->info("Test deleting a group");

        $id = 3;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_SEARCH);
        $group = GroupMock::createGroup($id, $user, "Group", "Group to delete");

        $this->groupManager->expects($this->once())->method("read")->with($id)->willReturn($group);
        $this->groupManager->expects($this->once())->method("delete")->with($group);

        $this->setAuthenticatedRequest($user);
        $this->client->request("DELETE", "/rest/groups/$id");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_OK, $response["code"]);
    }


    public function testDeleteGroupActionNotFound() {
        $this->logger->info("Test deleting a non existing group");

        $id = 3;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_SEARCH);

        $this->groupManager->expects($this->once())->method("read")->with($id)->willThrowException(
            new GroupNotFoundException("id", $id));
        $this->groupManager->expects($this->never())->method("delete");

        $this->setAuthenticatedRequest($user);
        $this->client->request("DELETE", "/rest/groups/$id");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_OK, $response["code"]);
    }


    public function testPatchGroupActionWith200() {
        $this->logger->info("Test patching a group with success");

        $id = 2;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_SEARCH);
        $group = GroupMock::createGroup($id, $user, "Group", "Group to update");
        $data = array ("description" => $group->getDescription(), "budget" => 942);
        $expectedGroup = GroupMock::createGroup($group->getId(), $group->getCreator(), $group->getName(),
            $data["description"]);
        $expectedGroup->setBudget($data["budget"]);

        $this->groupManager->expects($this->once())->method("read")->with($id)->willReturn($group);
        $this->groupManager->expects($this->once())->method("update")->with($group, $data, false)->willReturn(
            $expectedGroup);

        $this->setAuthenticatedRequest($user);
        $this->client->request("PATCH", "/rest/groups/$id", $data);
        $response = $this->getResponseContent();
        $updatedGroup = $response["rest"]["content"];

        $this->assertEquals(Response::HTTP_OK, $response["code"]);
        $this->assertEquals($expectedGroup->getId(), $updatedGroup["id"]);
        $this->assertEquals($expectedGroup->getName(), $updatedGroup["name"]);
        $this->assertEquals($expectedGroup->getBudget(), $updatedGroup["budget"]);
    }


    public function testPatchingGroupActionWith404() {
        $this->logger->info("Test patching a non existing group");

        $id = 2;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_SEARCH);
        $data = array ("description" => null, "budget" => 942);

        $this->groupManager->expects($this->once())->method("read")->willThrowException(
            new GroupNotFoundException("id", $id));
        $this->groupManager->expects($this->never())->method("update");

        $this->setAuthenticatedRequest($user);
        $this->client->request("PATCH", "/rest/groups/$id", $data);
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testPatchGroupActionWith400() {
        $this->logger->info("Test patching a group with bad request");

        $id = 2;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_SEARCH);
        $group = GroupMock::createGroup($id, $user, "Group", "Group to update");
        $data = array ("name" => null);

        $this->groupManager->expects($this->once())->method("read")->with($id)->willReturn($group);
        $this->groupManager->expects($this->once())->method("update")->with($group, $data, false)->willThrowException(
            new InvalidFormDataException("Exception from testPatchGroupWith400",
                $this->getForm(GroupType::class)->getErrors()));

        $this->setAuthenticatedRequest($user);
        $this->client->request("PATCH", "/rest/groups/$id", $data);
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response["code"]);
    }


    public function testSearchGroupsActionWith200() {
        $this->logger->info("Test searching groups by filtering with status code 200");

        $total = 30;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_SEARCH);
        $filter = new GroupFilter();
        $filter->setPage(2);
        $groups = GroupMock::createGroupPage($filter, $total);

        $this->groupManager->expects($this->once())->method("search")->with($filter)->willReturn($groups);
        $this->groupManager->expects($this->once())->method("countBy")->with($filter)->willReturn($total);

        $this->setAuthenticatedRequest($user);
        $this->client->request("POST", "/rest/groups/searches", array ("page" => 2));
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_OK, $response["code"]);
        $this->assertEquals(count($groups), count($response["rest"]["content"]));
    }


    public function testSearchGroupsActionWith206() {
        $this->logger->info("Test searching groups by filtering with status code 200");

        $total = 50;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_SEARCH);
        $filter = new GroupFilter();
        $groups = GroupMock::createGroupPage($filter, $total);

        $this->groupManager->expects($this->once())->method("search")->with($filter)->willReturn($groups);
        $this->groupManager->expects($this->once())->method("countBy")->with($filter)->willReturn($total);

        $this->setAuthenticatedRequest($user);
        $this->client->request("POST", "/rest/groups/searches");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_PARTIAL_CONTENT, $response["code"]);
        $this->assertEquals(count($groups), count($response["rest"]["content"]));
    }


    public function testGetMembersActionWith200() {
        $this->logger->info("Test getting the members of a group with status code 200");

        $id = 1;
        $nbMembers = 5;
        $expectedMembers = UserMock::createUserArray($nbMembers);
        $group = GroupMock::createGroup($id, $expectedMembers[0], "Group", "Get members group test");
        $group->setMembers(new ArrayCollection($expectedMembers));
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_SEARCH);

        $this->groupManager->expects($this->once())->method("read")->with($id)->willReturn($group);

        $this->setAuthenticatedRequest($user);
        $this->client->request("GET", "/rest/groups/$id/members");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_OK, $response["code"]);
        $this->assertEquals(count($expectedMembers), count($response["rest"]["content"]));
    }


    public function testGetMembersActionWith404() {
        $this->logger->info("Test getting the members of a non exisitng group");

        $id = 1;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_SEARCH);

        $this->groupManager->expects($this->once())->method("read")->with($id)->willThrowException(
            new GroupNotFoundException("id", $id));

        $this->setAuthenticatedRequest($user);
        $this->client->request("GET", "/rest/groups/$id/members");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testRemoveMemberActionWithSuccess() {
        $this->logger->info("Test removing a member of a group with success");

        $id = 1;
        $memberId = 2;
        $nbMembers = 5;
        $expectedMembers = UserMock::createUserArray($nbMembers);
        $group = GroupMock::createGroup($id, $expectedMembers[0], "Group", "Get members group test");
        $group->setMembers(new ArrayCollection($expectedMembers));
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_SEARCH);

        $this->groupManager->expects($this->once())->method("read")->with($id)->willReturn($group);
        $this->groupManager->expects($this->once())->method("removeMember")->with($group, $memberId);

        $this->setAuthenticatedRequest($user);
        $this->client->request("DELETE", "/rest/groups/$id/members/$memberId");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_OK, $response["code"]);
    }


    public function testRemoveMemberActionWithFailure() {
        $this->logger->info("Test removing a member of a non existing group");

        $id = 1;
        $memberId = 2;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_SEARCH);

        $this->groupManager->expects($this->once())->method("read")->with($id)->willThrowException(
            new GroupNotFoundException("id", $id));
        $this->groupManager->expects($this->never())->method("removeMember");

        $this->setAuthenticatedRequest($user);
        $this->client->request("DELETE", "/rest/groups/$id/members/$memberId");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testGetGroupPictureActionWith200() {
        $this->logger->info("Test getting the picture of a group with status code 200");

        $id = 1;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_SEARCH);
        $file = $this->createTempFile(dirname(__FILE__) . "/../../../Resources/uploads/image.jpg", "group-img.jpg");
        $group = GroupMock::createGroup($id, $user, "Group", "Get picture group test");
        $group->setPicture(GroupPictureMock::createPicture(1, $file, "picture.jpg"));

        $this->groupManager->expects($this->once())->method("read")->with($id)->willReturn($group);

        $this->setAuthenticatedRequest($user);
        $this->client->request("GET", "/rest/groups/$id/picture");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_OK, $response["code"]);
        $this->assertNotNull($response["rest"]["content"]);
    }


    public function testGetGroupPictureActionWith404() {
        $this->logger->info("Test getting the picture of a group with status code 404");

        $id = 1;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_SEARCH);

        $this->groupManager->expects($this->once())->method("read")->with($id)->willThrowException(
            new GroupNotFoundException("id", $id));

        $this->setAuthenticatedRequest($user);
        $this->client->request("GET", "/rest/groups/$id/picture");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testUploadGroupPictureActionWith200() {
        $this->logger->info("Test uploading a picture for a group with status code 200");

        $id = 1;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_SEARCH);
        $file = $this->createTempFile(dirname(__FILE__) . "/../../../Resources/uploads/image.jpg", "group-img.jpg");
        $group = GroupMock::createGroup($id, $user, "Group", "Get picture group test");
        $expectedPicture = GroupPictureMock::createPicture(1, $file, "picture.jpg");

        $this->groupManager->expects($this->once())->method("read")->with($id)->willReturn($group);
        $this->groupManager->expects($this->once())->method("uploadGroupPicture")->with($group, $file)->willReturn(
            $expectedPicture);

        $this->setAuthenticatedRequest($user);
        $this->client->request("POST", "/rest/groups/$id/picture", array (), array ("file" => $file));
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_OK, $response["code"]);
    }


    public function testUploadGroupPictureActionWith404() {
        $this->logger->info("Test uploading a picture for a group with status code 404");

        $id = 1;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_SEARCH);
        $file = $this->createTempFile(dirname(__FILE__) . "/../../../Resources/uploads/image.jpg", "group-img.jpg");

        $this->groupManager->expects($this->once())->method("read")->with($id)->willThrowException(
            new GroupNotFoundException("id", $id));
        $this->groupManager->expects($this->never())->method("uploadGroupPicture");

        $this->setAuthenticatedRequest($user);
        $this->client->request("POST", "/rest/groups/$id/picture", array (), array ("file" => $file));
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testDeleteGroupPictureActionWithSuccess() {
        $this->logger->info("Test deleting a picture of a group");

        $id = 1;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_SEARCH);
        $file = $this->createTempFile(dirname(__FILE__) . "/../../../Resources/uploads/image.jpg", "group-img.jpg");
        $group = GroupMock::createGroup($id, $user, "Group", "Get picture group test");
        $expectedPicture = GroupPictureMock::createPicture(1, $file, "picture.jpg");
        $group->setPicture($expectedPicture);

        $this->groupManager->expects($this->once())->method("read")->with($id)->willReturn($group);
        $this->groupManager->expects($this->once())->method("deleteGroupPicture")->with($group);

        $this->setAuthenticatedRequest($user);
        $this->client->request("DELETE", "/rest/groups/$id/picture");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_OK, $response["code"]);
    }


    public function testDeleteGroupPictureActionWithFailure() {
        $this->logger->info("Test deleting a picture of a group");

        $id = 1;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_SEARCH);

        $this->groupManager->expects($this->once())->method("read")->with($id)->willThrowException(
            new GroupNotFoundException("id", $id));
        $this->groupManager->expects($this->never())->method("deleteGroupPicture");

        $this->setAuthenticatedRequest($user);
        $this->client->request("DELETE", "/rest/groups/$id/picture");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }

}