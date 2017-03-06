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

        $username = "user@test.fr";
        $user = $this->createUser($username, "password", true);
        $this->userManager->expects($this->once())->method("findByUsername")->with($username)->willReturn($user);

        $this->client->request("POST", "/rest/auth-tokens/",
            array ("_username" => $username, "_password" => "password"));
        $response = $this->getResponseData();

        $this->assertEquals(Response::HTTP_CREATED, $response["code"]);

        $data = $response["content"];
        $this->assertNotEmpty($data["token"], "Expected 'token' field to be not empty");
        $this->assertNotEmpty($data["user"], "Expected 'user' field to be not empty");
        $this->assertEquals($username, $data["user"]["username"],
            sprintf("Expected username to be equal to '%s', but got'%s'", $username, $data["user"]["username"]));
    }


    public function testPostAuthTokenActionWith403() {
        $this->logger->info("Test authenticating a user with forbidden access");

        $username = "user@test.fr";
        $user = $this->createUser($username, "password", false);
        $this->userManager->expects($this->once())->method("findByUsername")->with($username)->willReturn($user);

        $this->client->request("POST", "/rest/auth-tokens/",
            array ("_username" => $username, "_password" => "password"));
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

        $username = "user@test.fr";
        $user = $this->createUser($username, "password", true);
        $this->userManager->expects($this->once())->method("findByUsername")->with($username)->willReturn($user);

        $this->client->request("POST", "/rest/auth-tokens/", array ("_username" => $username, "_password" => "toto"));
        $response = $this->getResponseData();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }

}