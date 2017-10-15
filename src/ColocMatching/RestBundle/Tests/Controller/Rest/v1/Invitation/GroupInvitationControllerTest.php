<?php

namespace ColocMatching\RestBundle\Tests\Controller\Rest\v1\Invitation;

use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\Invitation\Invitation;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Exception\AnnouncementNotFoundException;
use ColocMatching\CoreBundle\Exception\GroupNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Exception\InvitationNotFoundException;
use ColocMatching\CoreBundle\Form\Type\Invitation\InvitationType;
use ColocMatching\CoreBundle\Manager\Group\GroupManager;
use ColocMatching\CoreBundle\Manager\Invitation\InvitationManager;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Tests\Utils\Mock\Group\GroupMock;
use ColocMatching\CoreBundle\Tests\Utils\Mock\Invitation\InvitationMock;
use ColocMatching\CoreBundle\Tests\Utils\Mock\User\UserMock;
use ColocMatching\RestBundle\Tests\Controller\Rest\v1\RestTestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class GroupInvitationControllerTest extends RestTestCase {

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $invitationManager;

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
    private $mockGroup;

    /**
     * @var Invitation
     */
    private $mockInvitation;

    /**
     * @var User
     */
    private $authenticatedUser;


    protected function setUp() {
        parent::setUp();

        $this->invitationManager = $this->createMock(InvitationManager::class);
        $this->client->getContainer()->set("coloc_matching.core.group_invitation_manager",
            $this->invitationManager);

        $this->groupManager = $this->createMock(GroupManager::class);
        $this->client->getContainer()->set("coloc_matching.core.group_manager", $this->groupManager);

        $this->logger = $this->client->getContainer()->get("logger");

        $this->authenticatedUser = UserMock::createUser(1, "user@test.fr", "password", "User", "Test",
            UserConstants::TYPE_SEARCH);
        $this->setAuthenticatedRequest($this->authenticatedUser);

        $this->initMocks();
    }


    private function initMocks() {
        $this->mockGroup = GroupMock::createGroup(1,
            UserMock::createUser(10, "search@test.fr", "password", "Search", "Test", UserConstants::TYPE_SEARCH),
            "Group test", "Group from test");
        $this->groupManager->method("read")->with($this->mockGroup->getId())->willReturn($this->mockGroup);

        $this->mockInvitation = InvitationMock::createInvitation(1, $this->mockGroup, $this->authenticatedUser,
            Invitation::SOURCE_SEARCH);
        $this->invitationManager->method("read")->with($this->mockInvitation->getId())
            ->willReturn($this->mockInvitation);
    }


    protected function tearDown() {
        $this->logger->info("End test");
    }


    public function testGetInvitationsActionWith200() {
        $this->logger->info("Test getting invitations of a group with status code 200");

        $total = 30;
        $filter = new PageableFilter();
        $filter->setPage(2);
        $invitations = InvitationMock::createInvitationPageForInvitable($filter, $total, $this->mockGroup);

        $this->invitationManager->expects(self::once())->method("listByInvitable")->with($this->mockGroup, $filter)
            ->willReturn($invitations);
        $this->invitationManager->expects(self::once())->method("countByInvitable")->with($this->mockGroup)
            ->willReturn($total);

        $this->client->request("GET", "/rest/groups/" . $this->mockGroup->getId() . "/invitations",
            array ("page" => 2));
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_OK, $response["code"]);
        self::assertCount(count($invitations), $response["rest"]["content"]);
        self::assertEquals($filter->getSize(), $response["rest"]["size"]);
    }


    public function testGetInvitationsActionWith206() {
        $this->logger->info("Test getting invitations of a group with status code 206");

        $total = 30;
        $filter = new PageableFilter();
        $invitations = InvitationMock::createInvitationPageForInvitable($filter, $total, $this->mockGroup);

        $this->invitationManager->expects(self::once())->method("listByInvitable")->with($this->mockGroup, $filter)
            ->willReturn($invitations);
        $this->invitationManager->expects(self::once())->method("countByInvitable")->with($this->mockGroup)
            ->willReturn($total);

        $this->client->request("GET", sprintf("/rest/groups/%d/invitations", $this->mockGroup->getId()));
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_PARTIAL_CONTENT, $response["code"]);
        self::assertCount(count($invitations), $response["rest"]["content"]);
    }


    public function testGetInvitationsActionWith404() {
        $this->logger->info("Test getting invitations of a group with status code 404");

        $this->groupManager->expects(self::once())->method("read")->with($this->mockGroup->getId())
            ->willThrowException(new GroupNotFoundException("id", $this->mockGroup->getId()));
        $this->invitationManager->expects(self::never())->method("listByInvitable");
        $this->invitationManager->expects(self::never())->method("countByInvitable");

        $this->client->request("GET", sprintf("/rest/groups/%d/invitations", $this->mockGroup->getId()));
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testCreateInvitationActionWith201() {
        $this->logger->info("Test creating an invitation with status code 201");

        $data = array ("message" => "Invitation message");
        $this->mockInvitation->setMessage($data["message"]);

        $this->invitationManager->expects(self::once())->method("create")->with($this->mockGroup,
            $this->authenticatedUser, Invitation::SOURCE_SEARCH, $data)->willReturn($this->mockInvitation);

        $this->client->request("POST", sprintf("/rest/groups/%d/invitations", $this->mockGroup->getId()),
            $data);
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_CREATED, $response["code"]);
    }


    public function testCreateInvitationWith400() {
        $this->logger->info("Test creating an invitation with status code 400");

        $data = array ("unknownData" => 1230);

        $this->invitationManager->expects(self::once())->method("create")->with($this->mockGroup,
            $this->authenticatedUser, Invitation::SOURCE_SEARCH, $data)
            ->willThrowException(new InvalidFormException("Exception from test",
                $this->getForm(InvitationType::class)->getErrors()));

        $this->client->request("POST", sprintf("/rest/groups/%d/invitations", $this->mockGroup->getId()), $data);
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_BAD_REQUEST, $response["code"]);
    }


    public function testCreateInvitationActionWith403() {
        $this->logger->info("Test creating an invitation with status code 403");

        $this->authenticatedUser->setType(UserConstants::TYPE_PROPOSAL);

        $this->groupManager->expects(self::never())->method("read");
        $this->invitationManager->expects(self::never())->method("create");

        $this->client->request("POST", sprintf("/rest/groups/%d/invitations", $this->mockGroup->getId()));
        $response = $this->client->getResponse();

        self::assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }


    public function testCreateInvitationActionWith404() {
        $this->logger->info("Test creating an invitation with status code 404");

        $this->groupManager->expects(self::once())->method("read")->with($this->mockGroup->getId())
            ->willThrowException(new AnnouncementNotFoundException("id", $this->mockGroup->getId()));
        $this->invitationManager->expects(self::never())->method("create");

        $this->client->request("POST", sprintf("/rest/groups/%d/invitations", $this->mockGroup->getId()));
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testCreateInvitationActionWith422() {
        $this->logger->info("Test creating an invitation with status code 422");

        $data = array ("message" => "This is a message");
        $this->mockGroup->setStatus(Group::STATUS_CLOSED);

        $this->invitationManager->expects(self::once())->method("create")->with($this->mockGroup,
            $this->authenticatedUser, Invitation::SOURCE_SEARCH, $data)
            ->willThrowException(new UnprocessableEntityHttpException("Exception from test"));

        $this->client->request("POST", sprintf("/rest/groups/%d/invitations", $this->mockGroup->getId()),
            $data);
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response["code"]);
    }


    public function testGetInvitationActionWith200() {
        $this->logger->info("Test getting an invitation with status code 200");

        $this->client->request("GET", sprintf("/rest/groups/%d/invitations/%d", $this->mockGroup->getId(),
            $this->mockInvitation->getId()));
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_OK, $response["code"]);
    }


    public function testGetInvitationActionWith404OnInvitation() {
        $this->logger->info("Test getting an invitation with status code 404 on invitation");

        $this->invitationManager->expects(self::once())->method("read")->with($this->mockInvitation->getId())
            ->willThrowException(new InvitationNotFoundException("id", $this->mockInvitation->getId()));

        $this->client->request("GET", sprintf("/rest/groups/%d/invitations/%d", $this->mockGroup->getId(),
            $this->mockInvitation->getId()));
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testGetInvitationActionWith404OnGroup() {
        $this->logger->info("Test getting an invitation with status code 404 on group");

        $this->mockInvitation->setInvitable(GroupMock::createGroup(10,
            UserMock::createUser(10, "search@test.fr", "password", "Search", "Test", UserConstants::TYPE_SEARCH),
            "Group test", "Group from test"));

        $this->client->request("GET", sprintf("/rest/groups/%d/invitations/%d", $this->mockGroup->getId(),
            $this->mockInvitation->getId()));
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testGetInvitationActionWith404OnReadGroup() {
        $this->logger->info("Test getting an invitation with status code 404 on read group");

        $this->groupManager->expects(self::once())->method("read")->with($this->mockGroup->getId())
            ->willThrowException(new GroupNotFoundException("id", $this->mockGroup->getId()));
        $this->invitationManager->expects(self::never())->method("read");

        $this->client->request("GET", sprintf("/rest/groups/%d/invitations/%d", $this->mockGroup->getId(),
            $this->mockInvitation->getId()));
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testDeleteInvitationActionWith200() {
        $this->logger->info("Test deleting an invitation with status code 200");

        $this->invitationManager->expects(self::once())->method("delete")->with($this->mockInvitation);

        $this->client->request("DELETE", sprintf("/rest/groups/%d/invitations/%d", $this->mockGroup->getId(),
            $this->mockInvitation->getId()));
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_OK, $response["code"]);
    }


    public function testDeleteInvitationActionWithFailure() {
        $this->logger->info("Test deleting an invitation with failure");

        $this->invitationManager->expects(self::once())->method("read")->with($this->mockInvitation->getId())
            ->willThrowException(new InvitationNotFoundException("id", $this->mockInvitation->getId()));
        $this->invitationManager->expects(self::never())->method("delete");

        $this->client->request("DELETE", sprintf("/rest/groups/%d/invitations/%d", $this->mockGroup->getId(),
            $this->mockInvitation->getId()));
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_OK, $response["code"]);
    }


    public function testDeleteInvitationActionWith404() {
        $this->logger->info("Test deleting an invitation with status code 404");

        $this->groupManager->expects(self::once())->method("read")->with($this->mockGroup->getId())
            ->willThrowException(new GroupNotFoundException("id", $this->mockGroup->getId()));
        $this->invitationManager->expects(self::never())->method("delete");

        $this->client->request("DELETE", sprintf("/rest/groups/%d/invitations/%d", $this->mockGroup->getId(),
            $this->mockInvitation->getId()));
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testAnswerInvitationActionWith200() {
        $this->logger->info("Test answering an invitation with status code 200");

        $data = array ("accepted" => true);
        $this->mockInvitation->setSourceType(Invitation::SOURCE_INVITABLE);

        $this->invitationManager->expects(self::once())->method("answer")->with($this->mockInvitation,
            $data["accepted"]);

        $this->client->request("POST", sprintf("/rest/groups/%d/invitations/%d/answer", $this->mockGroup->getId(),
            $this->mockInvitation->getId()), $data);
        $response = $this->client->getResponse();

        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }


    public function testAnswerInvitationActionWith403() {
        $this->logger->info("Test answering an invitation with status code 403");

        $this->invitationManager->expects(self::never())->method("answer");

        $this->client->request("POST", sprintf("/rest/groups/%d/invitations/%d/answer", $this->mockGroup->getId(),
            $this->mockInvitation->getId()), array ("accepted" => true));
        $response = $this->client->getResponse();

        self::assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }


    public function testAnswerInvitationActionWith422() {
        $this->logger->info("Test answering an invitation with status code 422");

        $data = array ("accepted" => true);
        $this->mockInvitation->setSourceType(Invitation::SOURCE_INVITABLE);

        $this->invitationManager->expects(self::once())->method("answer")->with($this->mockInvitation,
            $data["accepted"])->willThrowException(new UnprocessableEntityHttpException("Exception from test"));

        $this->client->request("POST", sprintf("/rest/groups/%d/invitations/%d/answer", $this->mockGroup->getId(),
            $this->mockInvitation->getId()), $data);
        $response = $this->client->getResponse();

        self::assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }
}