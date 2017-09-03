<?php

namespace ColocMatching\RestBundle\Tests\Controller\Rest\v1\User;

use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Form\Type\User\UserType;
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
     * @var User
     */
    private $authenticatedUser;


    protected function setUp() {
        parent::setUp();

        $this->logger = $this->client->getContainer()->get("logger");
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


    public function testUpdateSelfActionWith400() {
        $this->logger->info("Test updating the connected user with status code 400");

        $data = array (
            "email" => $this->authenticatedUser->getEmail(),
            "plainPassword" => "new password",
            "type" => "unknown",
            "status" => "unknown",
            "firstname" => $this->authenticatedUser->getFirstname(),
            "lastname" => $this->authenticatedUser->getLastname()
        );

        $this->userManager->expects(self::once())->method("update")->with($this->authenticatedUser, $data,
            true)->willThrowException(new InvalidFormDataException("Exception from test",
            $this->getForm(UserType::class)->getErrors()));

        $this->setAuthenticatedRequest($this->authenticatedUser);
        $this->client->request("PUT", "/rest/me", $data);
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_BAD_REQUEST, $response["code"]);
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


    public function testPatchSelfActionWith400() {
        $this->logger->info("Test patching the connected user with status code 400");

        $data = array ("type" => "unknown", "status" => "unknown");

        $this->userManager->expects(self::once())->method("update")->with($this->authenticatedUser, $data,
            false)->willThrowException(new InvalidFormDataException("Exception from test",
            $this->getForm(UserType::class)->getErrors()));

        $this->setAuthenticatedRequest($this->authenticatedUser);
        $this->client->request("PATCH", "/rest/me", $data);
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_BAD_REQUEST, $response["code"]);
    }


    public function testPatchSelfActionWith401() {
        $this->logger->info("Test updating a non connected user");

        $this->userManager->expects(self::never())->method("update");

        $this->client->request("PATCH", "/rest/me");
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_UNAUTHORIZED, $response["code"]);
    }
}