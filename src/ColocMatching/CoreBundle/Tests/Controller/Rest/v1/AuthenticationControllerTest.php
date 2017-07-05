<?php

namespace ColocMatching\CoreBundle\Tests\Controller\Rest\v1;

use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Exception\UserNotFoundException;
use ColocMatching\CoreBundle\Manager\User\UserManager;
use ColocMatching\CoreBundle\Tests\Utils\Mock\User\UserMock;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

class AuthenticationControllerTest extends RestTestCase {

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $userManager;

    /**
     * @var LoggerInterface
     */
    private $logger;


    protected function setUp() {
        parent::setUp();

        $this->userManager = self::createMock(UserManager::class);
        $this->client->getContainer()->set("coloc_matching.core.user_manager", $this->userManager);
        $this->logger = $this->client->getContainer()->get("logger");
    }


    public function testPostAuthTokenActionWith201() {
        $this->logger->info("Test authenticating a user with success");

        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);
        $user->setStatus(UserConstants::STATUS_ENABLED);

        $this->userManager->expects($this->once())->method("findByUsername")->with($user->getUsername())->willReturn(
            $user);

        $this->client->request("POST", "/rest/auth-tokens/",
            array ("_username" => $user->getUsername(), "_password" => $user->getPlainPassword()));
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_CREATED, $response["code"]);

        $data = $response["rest"];
        $this->assertNotEmpty($data["token"], "Expected 'token' field to be not empty");
        $this->assertNotEmpty($data["user"], "Expected 'user' field to be not empty");
        $this->assertEquals($user->getUsername(), $data["user"]["username"],
            sprintf("Expected username to be equal to '%s', but got'%s'", $user->getUsername(),
                $data["user"]["username"]));
    }


    public function testPostAuthTokenActionWith403() {
        $this->logger->info("Test authenticating a user with forbidden access");

        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);
        $user->setStatus(UserConstants::STATUS_PENDING);

        $this->userManager->expects($this->once())->method("findByUsername")->with($user->getUsername())->willReturn(
            $user);

        $this->client->request("POST", "/rest/auth-tokens/",
            array ("_username" => $user->getUsername(), "_password" => $user->getPlainPassword()));
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_FORBIDDEN, $response["code"]);
    }


    public function testPostAuthTokenActionOnNonExistingUser() {
        $this->logger->info("Test authenticating a non existing user");

        $username = "user@test.fr";

        $this->userManager->expects($this->once())->method("findByUsername")->with($username)->willThrowException(
            new UserNotFoundException("username", $username));

        $this->client->request("POST", "/rest/auth-tokens/",
            array ("_username" => $username, "_password" => "password"));
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
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

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }

}