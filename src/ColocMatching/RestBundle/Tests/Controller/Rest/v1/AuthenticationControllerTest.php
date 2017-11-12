<?php

namespace ColocMatching\RestBundle\Tests\Controller\Rest\v1;

use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Exception\UserNotFoundException;
use ColocMatching\CoreBundle\Tests\Utils\Mock\User\UserMock;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

class AuthenticationControllerTest extends RestTestCase {

    /**
     * @var LoggerInterface
     */
    private $logger;


    protected function setUp() {
        parent::setUp();

        $this->logger = $this->client->getContainer()->get("logger");
    }


    public function testPostAuthTokenActionWith200() {
        $this->logger->info("Test authenticating a user with success");

        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);
        $user->setStatus(UserConstants::STATUS_ENABLED);

        $this->userManager->expects($this->once())->method("findByUsername")->with($user->getUsername())->willReturn(
            $user);

        $this->client->request("POST", "/rest/auth-tokens/",
            array ("_username" => $user->getUsername(), "_password" => $user->getPlainPassword()));
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_OK, $response["code"]);
    }


    public function testPostAuthTokenActionWith401() {
        $this->logger->info("Test authenticating a non valid user");

        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);
        $user->setStatus(UserConstants::STATUS_PENDING);

        $this->userManager->expects($this->once())->method("findByUsername")->with($user->getUsername())->willReturn(
            $user);

        $this->client->request("POST", "/rest/auth-tokens/",
            array ("_username" => $user->getUsername(), "_password" => $user->getPlainPassword()));
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response["code"]);
    }


    public function testPostAuthTokenActionOnNonExistingUser() {
        $this->logger->info("Test authenticating a non existing user");

        $username = "user@test.fr";

        $this->userManager->expects($this->once())->method("findByUsername")->with($username)->willThrowException(
            new UserNotFoundException("username", $username));

        $this->client->request("POST", "/rest/auth-tokens/",
            array ("_username" => $username, "_password" => "password"));
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response["code"]);
    }


    public function testPostAuthTokenActionWithBadCredentials() {
        $this->logger->info("Test authenticating a user with bad credentials");

        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);
        $user->setStatus(UserConstants::STATUS_ENABLED);

        $this->userManager->expects($this->once())->method("findByUsername")->with($user->getUsername())->willReturn(
            $user);

        $this->client->request("POST", "/rest/auth-tokens/",
            array ("_username" => $user->getUsername(), "_password" => "other password"));
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response["code"]);
    }

}