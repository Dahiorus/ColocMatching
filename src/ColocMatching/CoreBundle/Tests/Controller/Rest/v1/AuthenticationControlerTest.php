<?php

namespace ColocMatching\CoreBundle\Tests\Controller\Rest\v1;

use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\UserNotFoundException;
use ColocMatching\CoreBundle\Manager\User\UserManager;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class AuthenticationControlerTest extends WebTestCase {

    /**
     * @var Client
     */
    private $client;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $userManager;


    protected function setUp() {
        $this->client = parent::createClient();
        $this->client->setServerParameter("HTTP_HOST", "coloc-matching.api");

        $this->userManager = parent::createMock(UserManager::class);
        $this->client->getKernel()->getContainer()->set("coloc_matching.core.user_manager", $this->userManager);

        $this->logger = $this->client->getKernel()->getContainer()->get("logger");
    }


    public function testPostAuthTokenActionWith201() {
        $this->logger->info("Test authenticating a user with success");

        $username = "user@test.fr";
        $user = $this->createUser($username, true);
        $this->userManager->expects($this->once())->method("findByUsername")->with($username)->willReturn($user);

        $this->client->request("POST", "/rest/auth-tokens/",
            array ("_username" => $username, "_password" => "password"));

        /** @var Response */
        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode(),
            sprintf("Expected response status code to be equal to 201, but got %d", $response->getStatusCode()));

        $this->assertNotEmpty($data["token"], "Expected 'token' field to be not empty");
        $this->assertNotEmpty($data["user"], "Expected 'user' field to be not empty");
        $this->assertEquals($username, $data["user"]["username"],
            sprintf("Expected username to be equal to '%s', but got'%s'", $username, $data["user"]["username"]));
    }


    public function testPostAuthTokenActionWith403() {
        $this->logger->info("Test authenticating a user with forbidden access");

        $username = "user@test.fr";
        $user = $this->createUser($username, false);
        $this->userManager->expects($this->once())->method("findByUsername")->with($username)->willReturn($user);

        $this->client->request("POST", "/rest/auth-tokens/",
            array ("_username" => $username, "_password" => "password"));

        /** @var Response */
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode(),
            sprintf("Expected response status code to be equal to 403, but got %d", $response->getStatusCode()));
    }


    public function testPostAuthTokenActionOnNonExistingUser() {
        $this->logger->info("Test authenticating a non existing user");

        $username = "user@test.fr";
        $this->userManager->expects($this->once())->method("findByUsername")->with($username)->willThrowException(
            new UserNotFoundException("username", $username));

        $this->client->request("POST", "/rest/auth-tokens/",
            array ("_username" => $username, "_password" => "password"));

        /** @var Response */
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode(),
            sprintf("Expected response status code to be equal to 404, but got %d", $response->getStatusCode()));
    }


    public function testPostAuthTokenActionWithBadCredentials() {
        $this->logger->info("Test authenticating a user with bad credentials");

        $username = "user@test.fr";
        $user = $this->createUser($username, true);
        $this->userManager->expects($this->once())->method("findByUsername")->with($username)->willReturn($user);

        $this->client->request("POST", "/rest/auth-tokens/", array ("_username" => $username, "_password" => "toto"));

        /** @var Response */
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode(),
            sprintf("Expected response status code to be equal to 404, but got %d", $response->getStatusCode()));
    }


    private function createUser(string $username, bool $enabled): User {
        $user = new User();

        $user->setEmail($username);
        $user->setFirstname("User");
        $user->setLastname("Test");
        $user->setPassword(password_hash("password", PASSWORD_BCRYPT, [ "cost" => 12]));
        $user->setEnabled($enabled);

        return $user;
    }

}