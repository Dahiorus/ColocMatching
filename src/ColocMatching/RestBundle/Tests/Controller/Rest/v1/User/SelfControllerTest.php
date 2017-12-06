<?php

namespace ColocMatching\RestBundle\Tests\Controller\Rest\v1\User;

use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Form\Type\User\UserType;
use ColocMatching\CoreBundle\Manager\Message\PrivateConversationManager;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Tests\Utils\Mock\Message\PrivateConversationMock;
use ColocMatching\CoreBundle\Tests\Utils\Mock\User\UserMock;
use ColocMatching\RestBundle\Tests\Controller\Rest\v1\RestTestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

class SelfControllerTest extends RestTestCase {

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $privateConversationManager;

    /**
     * @var User
     */
    private $authenticatedUser;


    protected function setUp() {
        parent::setUp();

        $this->logger = $this->client->getContainer()->get("logger");

        $this->privateConversationManager = $this->createMock(PrivateConversationManager::class);
        $this->client->getContainer()->set("coloc_matching.core.private_conversation_manager",
            $this->privateConversationManager);

        $this->authenticatedUser = UserMock::createUser(1, "user@test.fr", "password123", "User", "Test",
            UserConstants::TYPE_SEARCH);
    }


    public function testGetSelfActionWith200() {
        $this->logger->info("Test getting the connected user with status code 200");

        $this->setAuthenticatedRequest($this->authenticatedUser);
        $this->client->request("GET", "/rest/me");
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_OK, $response["code"]);
    }


    public function testGetSelfActionWith401() {
        $this->logger->info("Test getting an non connected user");

        $this->client->request("GET", "/rest/me");
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_UNAUTHORIZED, $response["code"]);
    }


    public function testUpdateSelfActionWith200() {
        $this->logger->info("Test updating the connected user with status code 200");

        $data = array (
            "email" => $this->authenticatedUser->getEmail(),
            "plainPassword" => "new password",
            "type" => UserConstants::TYPE_PROPOSAL,
            "status" => $this->authenticatedUser->getStatus(),
            "firstname" => $this->authenticatedUser->getFirstname(),
            "lastname" => $this->authenticatedUser->getLastname()
        );
        $expectedUser = UserMock::createUser($this->authenticatedUser->getId(), $data["email"],
            $data["plainPassword"], $data["firstname"], $data["lastname"], $data["type"]);
        $expectedUser->setStatus($data["status"]);

        $this->userManager->expects(self::once())->method("update")->with($this->authenticatedUser, $data,
            true)->willReturn($expectedUser);

        $this->setAuthenticatedRequest($this->authenticatedUser);
        $this->client->request("PUT", "/rest/me", $data);
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_OK, $response["code"]);
    }


    public function testUpdateSelfActionWith422() {
        $this->logger->info("Test updating the connected user with status code 422");

        $data = array (
            "email" => $this->authenticatedUser->getEmail(),
            "plainPassword" => "new password",
            "type" => "unknown",
            "status" => "unknown",
            "firstname" => $this->authenticatedUser->getFirstname(),
            "lastname" => $this->authenticatedUser->getLastname()
        );

        $this->userManager->expects(self::once())->method("update")->with($this->authenticatedUser, $data,
            true)->willThrowException(new InvalidFormException("Exception from test",
            $this->getForm(UserType::class)->getErrors()));

        $this->setAuthenticatedRequest($this->authenticatedUser);
        $this->client->request("PUT", "/rest/me", $data);
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response["code"]);
    }


    public function testUpdateSelfActionWith401() {
        $this->logger->info("Test updating a non connected user");

        $this->userManager->expects(self::never())->method("update");

        $this->client->request("PUT", "/rest/me");
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_UNAUTHORIZED, $response["code"]);
    }


    public function testPatchSelfActionWith200() {
        $this->logger->info("Test patching the connected user with status code 200");

        $data = array ("type" => UserConstants::TYPE_PROPOSAL);
        $expectedUser = UserMock::createUser($this->authenticatedUser->getId(), $this->authenticatedUser->getEmail(),
            $this->authenticatedUser->getPlainPassword(), $this->authenticatedUser->getFirstname(),
            $this->authenticatedUser->getLastname(), $data["type"]);

        $this->userManager->expects(self::once())->method("update")->with($this->authenticatedUser, $data,
            false)->willReturn($expectedUser);

        $this->setAuthenticatedRequest($this->authenticatedUser);
        $this->client->request("PATCH", "/rest/me", $data);
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_OK, $response["code"]);
    }


    public function testPatchSelfActionWith422() {
        $this->logger->info("Test patching the connected user with status code 422");

        $data = array ("type" => "unknown", "status" => "unknown");

        $this->userManager->expects(self::once())->method("update")->with($this->authenticatedUser, $data,
            false)->willThrowException($this->createMock(InvalidFormException::class));

        $this->setAuthenticatedRequest($this->authenticatedUser);
        $this->client->request("PATCH", "/rest/me", $data);
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response["code"]);
    }


    public function testPatchSelfActionWith401() {
        $this->logger->info("Test patching a non connected user");

        $this->userManager->expects(self::never())->method("update");

        $this->client->request("PATCH", "/rest/me");
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_UNAUTHORIZED, $response["code"]);
    }


    public function testUpdateSelfStatusActionWith200() {
        $this->logger->info("Test updating the status of the connected user with status code 200");

        $status = UserConstants::STATUS_ENABLED;
        $this->authenticatedUser->setStatus(UserConstants::STATUS_VACATION);

        $this->userManager->expects(self::once())->method("updateStatus")->with($this->authenticatedUser,
            $status)->willReturn($this->authenticatedUser);

        $this->setAuthenticatedRequest($this->authenticatedUser);
        $this->client->request("PATCH", "/rest/me/status", array ("value" => $status));
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_OK, $response["code"]);
    }


    public function testUpdateSelfStatusWith400() {
        $this->logger->info("Test updating the status of the connected user with status code 400");

        $status = UserConstants::STATUS_BANNED;
        $this->authenticatedUser->setStatus(UserConstants::STATUS_VACATION);

        $this->userManager->expects(self::never())->method("updateStatus");

        $this->setAuthenticatedRequest($this->authenticatedUser);
        $this->client->request("PATCH", "/rest/me/status", array ("value" => $status));
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_BAD_REQUEST, $response["code"]);
    }


    public function testUpdatePasswordWith200() {
        $this->logger->info("Test updating the password of the authenticated user with status code 200");

        $data = array ("oldPassword" => $this->authenticatedUser->getPlainPassword(), "newPassword" => "new_password");

        $this->userManager->expects(self::once())->method("updatePassword")
            ->with($this->authenticatedUser, $data, true)
            ->willReturn($this->authenticatedUser);

        $this->setAuthenticatedRequest($this->authenticatedUser);
        $this->client->request("POST", "/rest/me/password", $data);
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_OK, $response["code"]);
    }


    public function testUpdatePasswordWith422() {
        $this->logger->info("Test updating the password of the authenticated user with status code 422");

        $data = array ("oldPassword" => "", "newPassword" => "new_password");

        $this->userManager->expects(self::once())->method("updatePassword")
            ->with($this->authenticatedUser, $data, true)
            ->willThrowException($this->createMock(InvalidFormException::class));

        $this->setAuthenticatedRequest($this->authenticatedUser);
        $this->client->request("POST", "/rest/me/password", $data);
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response["code"]);
    }


    public function testGetSelfPrivateConversationsWith206() {
        $this->logger->info("Test getting the private conversations of the authenticated user with status code 206");

        $total = 17;
        $filter = new PageableFilter();
        $filter->setSize(10)->setSort("lastUpdate")->setOrder(PageableFilter::ORDER_DESC);

        $this->privateConversationManager->expects(self::once())->method("findAll")
            ->with($this->authenticatedUser, $filter)
            ->willReturn(PrivateConversationMock::createPage($this->authenticatedUser, $filter, $total));
        $this->privateConversationManager->expects(self::once())->method("countAll")
            ->with($this->authenticatedUser)->willReturn($total);

        $this->setAuthenticatedRequest($this->authenticatedUser);
        $this->client->request("GET", "/rest/me/conversations");
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_PARTIAL_CONTENT, $response["code"]);
    }


    public function testGetSelfPrivateConversationsWith200() {
        $this->logger->info("Test getting the private conversations of the authenticated user with status code 200");

        $total = 17;
        $filter = new PageableFilter();
        $filter->setSize(20)->setSort("lastUpdate")->setOrder(PageableFilter::ORDER_DESC);

        $this->privateConversationManager->expects(self::once())->method("findAll")
            ->with($this->authenticatedUser, $filter)
            ->willReturn(PrivateConversationMock::createPage($this->authenticatedUser, $filter, $total));
        $this->privateConversationManager->expects(self::once())->method("countAll")
            ->with($this->authenticatedUser)->willReturn($total);

        $this->setAuthenticatedRequest($this->authenticatedUser);
        $this->client->request("GET", "/rest/me/conversations", array ("size" => $filter->getSize()));
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_OK, $response["code"]);
    }

}