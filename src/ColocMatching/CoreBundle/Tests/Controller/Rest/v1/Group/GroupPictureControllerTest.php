<?php

namespace ColocMatching\CoreBundle\Tests\Controller\Rest\v1\Group;

use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Exception\GroupNotFoundException;
use ColocMatching\CoreBundle\Manager\Group\GroupManager;
use ColocMatching\CoreBundle\Tests\Controller\Rest\v1\RestTestCase;
use ColocMatching\CoreBundle\Tests\Utils\Mock\Group\GroupMock;
use ColocMatching\CoreBundle\Tests\Utils\Mock\Group\GroupPictureMock;
use ColocMatching\CoreBundle\Tests\Utils\Mock\User\UserMock;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

class GroupPictureControllerTest extends RestTestCase {

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $groupManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Group
     */
    private $group;

    /**
     * @var User
     */
    private $authenticatedUser;


    protected function setUp() {
        parent::setUp();

        $this->groupManager = self::createMock(GroupManager::class);
        $this->client->getContainer()->set("coloc_matching.core.group_manager", $this->groupManager);

        $this->authenticatedUser = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_SEARCH);
        $this->setAuthenticatedRequest($this->authenticatedUser);

        $this->createGroupMock();

        $this->logger = $this->client->getContainer()->get("logger");
    }


    private function createGroupMock() {
        $file = $this->createTempFile(dirname(__FILE__) . "/../../../../Resources/uploads/image.jpg", "group-img.jpg");
        $user = UserMock::createUser(10, "group-creator@test.fr", "password", "User 2", "Test", UserConstants::TYPE_SEARCH);
        $this->group = GroupMock::createGroup(1, $user, "Group", "Get picture group test");
        $this->group->setPicture(GroupPictureMock::createPicture(1, $file, "picture.jpg"));

        $this->groupManager->expects($this->once())->method("read")->with($this->group->getId())->willReturn($this->group);
    }


    public function testGetGroupPictureActionWith200() {
        $id = $this->group->getId();

        $this->logger->info("Test getting the picture of a group with status code 200");

        $this->client->request("GET", "/rest/groups/$id/picture");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_OK, $response["code"]);
        $this->assertNotNull($response["rest"]["content"]);
    }


    public function testGetGroupPictureActionWith404() {
        $this->logger->info("Test getting the picture of a group with status code 404");

        $id = 1;

        $this->groupManager->expects($this->once())->method("read")->with($id)->willThrowException(
            new GroupNotFoundException("id", $id));

        $this->client->request("GET", "/rest/groups/$id/picture");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testUploadGroupPictureActionWith200() {
        $this->logger->info("Test uploading a picture for a group with status code 200");

        $id = $this->group->getId();
        $file = $this->createTempFile(dirname(__FILE__) . "/../../../../Resources/uploads/image.jpg", "group-img.jpg");
        $expectedPicture = GroupPictureMock::createPicture(1, $file, "picture.jpg");

        $this->groupManager->expects($this->once())->method("uploadGroupPicture")->with($this->group, $file)->willReturn(
            $expectedPicture);

        $this->client->request("POST", "/rest/groups/$id/picture", array (), array ("file" => $file));
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_OK, $response["code"]);
    }


    public function testUploadGroupPictureActionWith404() {
        $this->logger->info("Test uploading a picture for a group with status code 404");

        $id = 1;
        $file = $this->createTempFile(dirname(__FILE__) . "/../../../../Resources/uploads/image.jpg", "group-img.jpg");

        $this->groupManager->expects($this->once())->method("read")->with($id)->willThrowException(
            new GroupNotFoundException("id", $id));
        $this->groupManager->expects($this->never())->method("uploadGroupPicture");

        $this->client->request("POST", "/rest/groups/$id/picture", array (), array ("file" => $file));
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testDeleteGroupPictureActionWithSuccess() {
        $this->logger->info("Test deleting a picture of a group");

        $id = $this->group->getId();

        $this->client->request("DELETE", "/rest/groups/$id/picture");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_OK, $response["code"]);
    }


    public function testDeleteGroupPictureActionWithFailure() {
        $this->logger->info("Test deleting a picture of a group");

        $id = 1;

        $this->groupManager->expects($this->once())->method("read")->with($id)->willThrowException(
            new GroupNotFoundException("id", $id));
        $this->groupManager->expects($this->never())->method("deleteGroupPicture");

        $this->client->request("DELETE", "/rest/groups/$id/picture");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }
}