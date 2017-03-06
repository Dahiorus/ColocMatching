<?php

namespace ColocMatching\CoreBundle\Tests\Controller\Rest\v1;

use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\UserNotFoundException;
use ColocMatching\CoreBundle\Manager\User\UserManager;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

class AuthenticationControllerTest extends RestTestCase {


    public function testPostAuthTokenActionWith201() {
        $this->logger->info("Test authenticating a user with success");

        $user = $this->mockCreateUser("user@test.fr", "password", true);

        $this->client->request("POST", "/rest/auth-tokens/",
            array ("_username" => $user->getUsername(), "_password" => $user->getPlainPassword()));
        $response = $this->getResponseData();

        $this->assertEquals(Response::HTTP_CREATED, $response["code"]);

        $data = $response["content"];
        $this->assertNotEmpty($data["token"], "Expected 'token' field to be not empty");
        $this->assertNotEmpty($data["user"], "Expected 'user' field to be not empty");
        $this->assertEquals($user->getUsername(), $data["user"]["username"],
            sprintf("Expected username to be equal to '%s', but got'%s'", $user->getUsername(),
                $data["user"]["username"]));
    }


    public function testPostAuthTokenActionWith403() {
        $this->logger->info("Test authenticating a user with forbidden access");

        $user = $this->createUser("user@test.fr", "password", false);
        $this->userManager->expects($this->once())->method("findByUsername")->with($user->getUsername())->willReturn(
            $user);

        $this->client->request("POST", "/rest/auth-tokens/",
            array ("_username" => $user->getUsername(), "_password" => $user->getPlainPassword()));
        $response = $this->getResponseData();

        $this->assertEquals(Response::HTTP_FORBIDDEN, $response["code"]);
    }


    public function testPostAuthTokenActionOnNonExistingUser() {
        $this->logger->info("Test authenticating a non existing user");

        $username = "user@test.fr";
        $this->userManager->expects($this->once())->method("findByUsername")->with($username)->willThrowException(
            new UserNotFoundException("username", $username));

        $this->client->request("POST", "/rest/auth-tokens/",
            array ("_username" => $username, "_password" => "password"));
        $response = $this->getResponseData();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testPostAuthTokenActionWithBadCredentials() {
        $this->logger->info("Test authenticating a user with bad credentials");

        $user = $this->createUser("user@test.fr", "password", true);
        $this->userManager->expects($this->once())->method("findByUsername")->with($user->getUsername())->willReturn(
            $user);

        $this->client->request("POST", "/rest/auth-tokens/",
            array ("_username" => $user->getUsername(), "_password" => "toto"));
        $response = $this->getResponseData();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    private function mockCreateUser(string $email, string $plainPassword, bool $enabled): User {
        $user = $this->createUser($email, $plainPassword, $enabled);
        $this->userManager->expects($this->once())->method("findByUsername")->with($user->getUsername())->willReturn(
            $user);

        return $user;
    }

}