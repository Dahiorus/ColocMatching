<?php

namespace ColocMatching\RestBundle\Tests\Controller\Rest\v1\Invitation;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\Invitation\Invitable;
use ColocMatching\CoreBundle\Entity\Invitation\Invitation;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Exception\InvitationNotFoundException;
use ColocMatching\CoreBundle\Exception\UnavailableInvitableException;
use ColocMatching\CoreBundle\Exception\UserNotFoundException;
use ColocMatching\CoreBundle\Form\Type\Invitation\InvitationType;
use ColocMatching\CoreBundle\Manager\Invitation\InvitationManager;
use ColocMatching\CoreBundle\Repository\Filter\InvitationFilter;
use ColocMatching\CoreBundle\Tests\Utils\Mock\Invitation\InvitationMock;
use ColocMatching\CoreBundle\Tests\Utils\Mock\User\UserMock;
use ColocMatching\RestBundle\Tests\Controller\Rest\v1\RestTestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

abstract class UserInvitationControllerTest extends RestTestCase {

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $invitationManager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var User
     */
    protected $mockUser;

    /**
     * @var User
     */
    protected $authenticatedUser;

    /**
     * @var Invitation
     */
    protected $mockInvitation;

    /**
     * @var Invitable
     */
    protected $mockInvitable;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $managerId;


    protected function setUp() {
        parent::setUp();

        $this->invitationManager = $this->createMock(InvitationManager::class);
        $this->client->getContainer()->set($this->managerId, $this->invitationManager);

        $this->logger = $this->client->getContainer()->get("logger");
    }


    protected function initMocks() {
        $this->mockUser = UserMock::createUser(1, "recipient@test.fr", "password", "Recipient", "Test",
            UserConstants::TYPE_SEARCH);
        $this->userManager->method("read")->with($this->mockUser->getId())->willReturn($this->mockUser);

        $this->mockInvitation = InvitationMock::createInvitation(1, $this->mockInvitable, $this->mockUser,
            Invitation::SOURCE_INVITABLE);
        $this->invitationManager->method("read")->with($this->mockInvitation->getId())->willReturn($this->mockInvitation);

        $this->setAuthenticatedRequest($this->authenticatedUser);
    }


    protected function tearDown() {
        $this->logger->info("End test");
    }


    private function getInvitableClass(string $type) : string {
        $invitableClass = null;

        switch ($type) {
            case "announcement":
                $invitableClass = Announcement::class;
                break;
            case "group":
                $invitableClass = Group::class;
                break;
            default:
                throw new \Exception("Unknown invitable type");
        }

        return $invitableClass;
    }


    public function testGetInvitationsActionWith200() {
        $this->logger->info("Test getting invitations of a user with status code 200", array ("type" => $this->type));

        $total = 30;
        $filter = new InvitationFilter();
        $filter->setPage(2);
        $filter->setRecipientId($this->mockUser->getId());
        $invitations = InvitationMock::createInvitationPage($filter, $total, $this->getInvitableClass($this->type),
            $this->mockUser);

        $this->invitationManager->expects(self::once())->method("search")->with($filter)->willReturn($invitations);
        $this->invitationManager->expects(self::once())->method("countBy")->with($filter)->willReturn($total);

        $this->client->request("GET", sprintf("/rest/users/%d/invitations", $this->mockUser->getId()),
            array ("page" => 2, "type" => $this->type));
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_OK, $response["code"]);
        self::assertCount(count($invitations), $response["rest"]["content"]);
        self::assertEquals($filter->getSize(), $response["rest"]["size"]);
    }


    public function testGetInvitationsActionWith206() {
        $this->logger->info("Test getting invitations of a user with status code 206", array ("type" => $this->type));

        $total = 30;
        $filter = new InvitationFilter();
        $filter->setRecipientId($this->mockUser->getId());
        $invitations = InvitationMock::createInvitationPage($filter, $total, $this->getInvitableClass($this->type),
            $this->mockUser);

        $this->invitationManager->expects(self::once())->method("search")->with($filter)->willReturn($invitations);
        $this->invitationManager->expects(self::once())->method("countBy")->with($filter)->willReturn($total);

        $this->client->request("GET", sprintf("/rest/users/%d/invitations", $this->mockUser->getId()),
            array ("type" => $this->type));
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_PARTIAL_CONTENT, $response["code"]);
        self::assertCount(count($invitations), $response["rest"]["content"]);
    }


    public function testGetInvitationsActionWith404() {
        $this->logger->info("Test getting invitations of a user with status code 404", array ("type" => $this->type));

        $this->userManager->expects(self::once())->method("read")->with($this->mockUser->getId())
            ->willThrowException(new UserNotFoundException("id", $this->mockUser->getId()));
        $this->invitationManager->expects(self::never())->method("search");
        $this->invitationManager->expects(self::never())->method("countBy");

        $this->client->request("GET", sprintf("/rest/users/%d/invitations", $this->mockUser->getId()),
            array ("type" => $this->type));
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testCreateInvitationActionWith201() {
        $this->logger->info("Test creating an invitation with status code 201", array ("type" => $this->type));

        $data = array ("message" => "Invitation message");
        $this->mockInvitation->setMessage($data["message"]);

        $this->invitationManager->expects(self::once())->method("create")->with($this->mockInvitation->getInvitable(),
            $this->mockUser, Invitation::SOURCE_INVITABLE, $data)->willReturn($this->mockInvitation);

        $this->client->request("POST",
            sprintf("/rest/users/%d/invitations?type=%s", $this->mockUser->getId(), $this->type), $data);
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_CREATED, $response["code"]);
    }


    public function testCreateInvitationActionWith422() {
        $this->logger->info("Test creating an invitation with status code 422", array ("type" => $this->type));

        $data = array ("unknownData" => 1230);

        $this->invitationManager->expects(self::once())->method("create")->with($this->mockInvitation->getInvitable(),
            $this->mockUser, Invitation::SOURCE_INVITABLE, $data)
            ->willThrowException(new InvalidFormException("Exception from test",
                $this->getForm(InvitationType::class)->getErrors()));

        $this->client->request("POST",
            sprintf("/rest/users/%d/invitations?type=%s", $this->mockUser->getId(), $this->type), $data);
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response["code"]);
    }


    public function testCreateInvitationActionWith403() {
        $this->logger->info("Test creating an invitation with status code 403", array ("type" => $this->type));

        $this->authenticatedUser->setGroup(null);
        $this->authenticatedUser->setAnnouncement(null);

        $this->invitationManager->expects(self::never())->method("create");

        $this->client->request("POST",
            sprintf("/rest/users/%d/invitations?type=%s", $this->mockUser->getId(), $this->type));
        $response = $this->client->getResponse();

        self::assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }


    public function testCreateInvitationActionWith404() {
        $this->logger->info("Test creating an invitation with status code 404", array ("type" => $this->type));

        $this->userManager->expects(self::once())->method("read")->with($this->mockUser->getId())
            ->willThrowException(new UserNotFoundException("id", $this->mockUser->getId()));
        $this->invitationManager->expects(self::never())->method("create");

        $this->client->request("POST",
            sprintf("/rest/users/%d/invitations?type=%s", $this->mockUser->getId(), $this->type));
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testCreateInvitationActionWith400() {
        $this->logger->info("Test creating an invitation with status code 400", array ("type" => $this->type));

        $data = array ("message" => "This is a message");

        $this->invitationManager->expects(self::once())->method("create")->with($this->mockInvitation->getInvitable(),
            $this->mockUser, Invitation::SOURCE_INVITABLE, $data)
            ->willThrowException(new UnavailableInvitableException($this->mockInvitation->getInvitable()));

        $this->client->request("POST",
            sprintf("/rest/users/%d/invitations?type=%s", $this->mockUser->getId(), $this->type), $data);
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_BAD_REQUEST, $response["code"]);
    }


    public function testGetInvitationActionWith200() {
        $this->logger->info("Test getting an invitation with status code 200", array ("type" => $this->type));

        $this->client->request("GET", sprintf("/rest/users/%d/invitations/%d", $this->mockUser->getId(),
            $this->mockInvitation->getId()), array ("type" => $this->type));
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_OK, $response["code"]);
    }


    public function testGetInvitationActionWith404OnInvitation() {
        $this->logger->info("Test getting an invitation with status code 404 on invitation",
            array ("type" => $this->type));

        $this->invitationManager->expects(self::once())->method("read")->with($this->mockInvitation->getId())
            ->willThrowException(new InvitationNotFoundException("id", $this->mockInvitation->getId()));

        $this->client->request("GET", sprintf("/rest/users/%d/invitations/%d", $this->mockUser->getId(),
            $this->mockInvitation->getId()), array ("type" => $this->type));
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testGetInvitationActionWith404OnRecipient() {
        $this->logger->info("Test getting an invitation with status code 404 on recipient",
            array ("type" => $this->type));

        $this->mockInvitation->setRecipient(UserMock::createUser(99, "recipient@test.fr", "password", "Recipient",
            "Test", UserConstants::TYPE_SEARCH));

        $this->client->request("GET", sprintf("/rest/users/%d/invitations/%d", $this->mockUser->getId(),
            $this->mockInvitation->getId()), array ("type" => $this->type));
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testGetInvitationActionWith404OnReadUser() {
        $this->logger->info("Test getting an invitation with status code 404 on read user",
            array ("type" => $this->type));

        $this->userManager->expects(self::once())->method("read")->with($this->mockUser->getId())
            ->willThrowException(new UserNotFoundException("id", $this->mockUser->getId()));
        $this->invitationManager->expects(self::never())->method("read");

        $this->client->request("GET", sprintf("/rest/users/%d/invitations/%d", $this->mockUser->getId(),
            $this->mockInvitation->getId()), array ("type" => $this->type));
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testDeleteInvitationActionWith200() {
        $this->logger->info("Test deleting an invitation with status code 200", array ("type" => $this->type));

        $this->invitationManager->expects(self::once())->method("delete")->with($this->mockInvitation);

        $this->client->request("DELETE", sprintf("/rest/users/%d/invitations/%d?type=%s", $this->mockUser->getId(),
            $this->mockInvitation->getId(), $this->type));
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_OK, $response["code"]);
    }


    public function testDeleteInvitationActionWithFailure() {
        $this->logger->info("Test deleting an invitation with failure", array ("type" => $this->type));

        $this->invitationManager->expects(self::once())->method("read")->with($this->mockInvitation->getId())
            ->willThrowException(new InvitationNotFoundException("id", $this->mockInvitation->getId()));
        $this->invitationManager->expects(self::never())->method("delete");

        $this->client->request("DELETE", sprintf("/rest/users/%d/invitations/%d?type=%s", $this->mockUser->getId(),
            $this->mockInvitation->getId(), $this->type));
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_OK, $response["code"]);
    }


    public function testDeleteInvitationActionWith404() {
        $this->logger->info("Test deleting an invitation with status code 404", array ("type" => $this->type));

        $this->userManager->expects(self::once())->method("read")->with($this->mockUser->getId())
            ->willThrowException(new UserNotFoundException("id", $this->mockUser->getId()));
        $this->invitationManager->expects(self::never())->method("delete");

        $this->client->request("DELETE", sprintf("/rest/users/%d/invitations/%d?type=%s", $this->mockUser->getId(),
            $this->mockInvitation->getId(), $this->type));
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }
}