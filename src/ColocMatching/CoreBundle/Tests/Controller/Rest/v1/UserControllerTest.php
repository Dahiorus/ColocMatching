<?php

namespace ColocMatching\CoreBundle\Tests\Controller\Rest\v1;

use ColocMatching\CoreBundle\Controller\Rest\RequestConstants;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Exception\UserNotFoundException;
use ColocMatching\CoreBundle\Form\Type\User\UserType;
use ColocMatching\CoreBundle\Repository\Filter\UserFilter;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends RestTestCase {


    public function testGetUsersActionWith200() {
        $this->logger->info("Test getting users with status code 200");

        $size = RequestConstants::DEFAULT_LIMIT;
        $this->mockListUsers($size, $size);

        $this->client->request("GET", "/rest/users/");
        $response = $this->getResponseData();

        $this->assertEquals(Response::HTTP_OK, $response["code"]);

        $restList = $response["content"];
        $this->assertEquals($size, $restList["size"],
            sprintf("Expected to get an array of %d elements, but got %d", $size, $restList["size"]));
        $this->assertEquals($size, $restList["total"],
            sprintf("Expected total elements to equal to %d, but got %d", $size, $restList["total"]));
    }


    public function testGetUsersActionWith206() {
        $this->logger->info("Test getting users with status code 206");

        $size = RequestConstants::DEFAULT_LIMIT;
        $total = $size + 10;
        $this->mockListUsers($size, $total);

        $this->client->request("GET", "/rest/users/");
        $response = $this->getResponseData();

        $this->assertEquals(Response::HTTP_PARTIAL_CONTENT, $response["code"]);

        $restList = $response["content"];
        $this->assertEquals($size, $restList["size"],
            sprintf("Expected to get an array of %d elements, but got %d", $size, $restList["size"]));
    }


    public function testCreateUserActionWith201() {
        $this->logger->info("Test creating user with status code 201");

        $data = array (
            "email" => "user@test.fr",
            "plainPassword" => "password",
            "firstname" => "Toto",
            "lastname" => "Toto",
            "enabled" => true);
        $user = $this->createUser($data["email"], $data["plainPassword"], $data["enabled"]);
        $user->setFirstname($data["firstname"])->setLastname($data["lastname"]);
        $this->userManager->expects($this->once())->method("create")->with($data)->willReturn($user);

        $this->client->request("POST", "/rest/users/", $data);
        $response = $this->getResponseData();

        $this->assertEquals(Response::HTTP_CREATED, $response["code"]);

        $createdUser = $response["content"]["data"];

        $this->assertEquals($user->getEmail(), $createdUser["email"],
            sprintf("Expected user email to be equal to '%s', but got '%s'", $user->getEmail(), $createdUser["email"]));
        $this->assertEquals($user->getFirstname(), $createdUser["firstname"],
            sprintf("Expected user firstname to be equal to '%s', but got '%s'", $user->getFirstname(),
                $createdUser["firstname"]));
    }


    public function testCreateUserActionWith400() {
        $this->logger->info("Test creating user with status code 400");

        $data = array ("email" => "");
        $form = $this->createFormType(UserType::class);
        $this->userManager->expects($this->once())->method("create")->with($data)->willThrowException(
            new InvalidFormDataException("Invalid data submitted in the user form", $form->getErrors(true, true)));

        $this->client->request("POST", "/rest/users/", $data);
        $response = $this->getResponseData();

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response["code"]);
    }


    public function testGetUserActionWith200() {
        $this->logger->info("Test getting an existing user with status code 200");

        $id = 1;
        $authToken = $this->mockAuthToken($this->createUser("auth-user@test.fr", "password", true));

        $user = $this->createUser("user@test.fr", "password", true);
        $this->userManager->expects($this->once())->method("read")->with($id)->willReturn($user);

        $this->client->setServerParameter("HTTP_AUTHORIZATION", sprintf("Bearer %s", $authToken));
        $this->client->request("GET", "/rest/users/$id");
        $response = $this->getResponseData();

        $this->assertEquals(Response::HTTP_OK, $response["code"]);
        $this->assertNotNull($response["content"]["data"]);
    }


    public function testGetUserActionWith401() {
        $this->logger->info("Test getting an existing user with status code 401");

        $this->client->request("GET", "/rest/users/1");
        $response = $this->getResponseData();

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response["code"]);
    }


    public function testGetUserActionWith404() {
        $this->logger->info("Test getting an existing user with status code 404");

        $id = 1;

        $this->userManager->expects($this->once())->method("read")->with($id)->willThrowException(
            new UserNotFoundException("id", $id));
        $authToken = $this->mockAuthToken($this->createUser("auth-user@test.fr", "password", true));

        $this->client->setServerParameter("HTTP_AUTHORIZATION", sprintf("Bearer %s", $authToken));
        $this->client->request("GET", "/rest/users/$id");
        $response = $this->getResponseData();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testUpdateUserActionWith200() {
        $this->logger->info("Test updating an existing user with status code 200");

        $id = 1;
        $authToken = $this->mockAuthToken($this->createUser("auth-user@test.fr", "password", true));

        $data = array (
            "email" => "user@test.fr",
            "plainPassword" => "password",
            "firstname" => "Toto",
            "lastname" => "Toto");
        $user = $this->createUser($data["email"], $data["plainPassword"], true);
        $updatedUser = $this->createUser($data["email"], $data["plainPassword"], true);
        $updatedUser->setFirstname($data["lastname"])->setFirstname($data["firstname"]);

        $this->userManager->expects($this->once())->method("read")->with($id)->willReturn($user);
        $this->userManager->expects($this->once())->method("update")->with($user, $data)->willReturn($updatedUser);

        $this->client->setServerParameter("HTTP_AUTHORIZATION", sprintf("Bearer %s", $authToken));
        $this->client->request("PUT", "/rest/users/$id", $data);
        $response = $this->getResponseData();

        $this->assertEquals(Response::HTTP_OK, $response["code"]);

        $restData = $response["content"];
        $this->assertNotNull($restData["data"]);
        $this->assertEquals($updatedUser->getFirstname(), $restData["data"]["firstname"],
            sprintf("Expected user firstname to be equal to '%s', but got '%s'", $updatedUser->getFirstname(),
                $restData["data"]["firstname"]));
    }


    public function testUpdateUserActionWith400() {
        $this->logger->info("Test updating an existing user with status code 400");

        $id = 1;
        $authToken = $this->mockAuthToken($this->createUser("auth-user@test.fr", "password", true));
        $form = $this->createFormType(UserType::class);

        $data = array ("email" => "user@test.fr", "lastname" => "Toto");
        $user = $this->createUser($data["email"], "password", true);

        $this->userManager->expects($this->once())->method("read")->with($id)->willReturn($user);
        $this->userManager->expects($this->once())->method("update")->with($user, $data)->willThrowException(
            new InvalidFormDataException("message", $form->getErrors(true, true)));

        $this->client->setServerParameter("HTTP_AUTHORIZATION", sprintf("Bearer %s", $authToken));
        $this->client->request("PUT", "/rest/users/$id", $data);
        $response = $this->getResponseData();

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response["code"]);
    }


    public function testUpdateUserActionWith404() {
        $this->logger->info("Test updating an existing user with status code 404");

        $id = 1;
        $authToken = $this->mockAuthToken($this->createUser("auth-user@test.fr", "password", true));

        $data = array (
            "email" => "user@test.fr",
            "plainPassword" => "password",
            "firstname" => "Toto",
            "lastname" => "Toto");

        $this->userManager->expects($this->once())->method("read")->with($id)->willThrowException(
            new UserNotFoundException("id", $id));

        $this->client->setServerParameter("HTTP_AUTHORIZATION", sprintf("Bearer %s", $authToken));
        $this->client->request("PUT", "/rest/users/$id", $data);
        $response = $this->getResponseData();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testUpdateUserActionWith401() {
        $this->logger->info("Test updating an existing user with status code 401");

        $this->client->request("PUT", "/rest/users/1");
        $response = $this->getResponseData();

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response["code"]);
    }


    public function testPatchUserActionWith200() {
        $this->logger->info("Test patching an existing user with status code 200");

        $id = 1;
        $authToken = $this->mockAuthToken($this->createUser("auth-user@test.fr", "password", true));

        $data = array ("firstname" => "Toto");
        $user = $this->createUser("user@test.fr", "password", true);
        $patchedUser = $user;
        $patchedUser->setFirstname($data["firstname"]);

        $this->userManager->expects($this->once())->method("read")->with($id)->willReturn($user);
        $this->userManager->expects($this->once())->method("partialUpdate")->with($user, $data)->willReturn(
            $patchedUser);

        $this->client->setServerParameter("HTTP_AUTHORIZATION", sprintf("Bearer %s", $authToken));
        $this->client->request("PATCH", "/rest/users/$id", $data);
        $response = $this->getResponseData();

        $this->assertEquals(Response::HTTP_OK, $response["code"]);

        $restData = $response["content"];
        $this->assertNotNull($restData["data"]);
        $this->assertEquals($patchedUser->getFirstname(), $restData["data"]["firstname"],
            sprintf("Expected user firstname to be equal to '%s', but got '%s'", $data["firstname"],
                $restData["data"]["firstname"]));
    }


    public function testPatchUserActionWith400() {
        $this->logger->info("Test patching an existing user with status code 400");

        $id = 1;
        $authToken = $this->mockAuthToken($this->createUser("auth-user@test.fr", "password", true));
        $form = $this->createFormType(UserType::class);

        $data = array ("email" => "user", "lastname" => "Toto");
        $user = $this->createUser("user@test.fr", "password", true);

        $this->userManager->expects($this->once())->method("read")->with($id)->willReturn($user);
        $this->userManager->expects($this->once())->method("partialUpdate")->with($user, $data)->willThrowException(
            new InvalidFormDataException("message", $form->getErrors(true, true)));

        $this->client->setServerParameter("HTTP_AUTHORIZATION", sprintf("Bearer %s", $authToken));
        $this->client->request("PATCH", "/rest/users/$id", $data);
        $response = $this->getResponseData();

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response["code"]);
    }


    public function testPatchUserActionWith404() {
        $this->logger->info("Test patching an existing user with status code 404");

        $id = 1;
        $authToken = $this->mockAuthToken($this->createUser("auth-user@test.fr", "password", true));

        $data = array ("lastname" => "Toto");

        $this->userManager->expects($this->once())->method("read")->with($id)->willThrowException(
            new UserNotFoundException("id", $id));

        $this->client->setServerParameter("HTTP_AUTHORIZATION", sprintf("Bearer %s", $authToken));
        $this->client->request("PATCH", "/rest/users/$id", $data);
        $response = $this->getResponseData();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testPatchUserActionWith401() {
        $this->logger->info("Test patching an existing user with status code 401");

        $this->client->request("PATCH", "/rest/users/1");
        $response = $this->getResponseData();

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response["code"]);
    }


    public function testDeleteUserActionWith200() {
        $this->logger->info("Test deleting an existing user with status code 200");

        $id = 1;
        $admin = $this->createUser("admin@test.fr", "password", true);
        $admin->addRole("ROLE_ADMIN");
        $authToken = $this->mockAuthToken($admin);

        $user = $this->createUser("user@test.fr", "password", true);
        $this->userManager->expects($this->once())->method("read")->with($id)->willReturn($user);

        $this->client->setServerParameter("HTTP_AUTHORIZATION", sprintf("Bearer %s", $authToken));
        $this->client->request("DELETE", "/rest/users/$id");
        $response = $this->getResponseData();

        $this->assertEquals(Response::HTTP_OK, $response["code"]);
    }


    public function testDeleteUserActionWith403() {
        $this->logger->info("Test deleting an existing user with status code 403");

        $id = 1;
        $admin = $this->createUser("admin@test.fr", "password", true);
        $authToken = $this->mockAuthToken($admin);

        $this->client->setServerParameter("HTTP_AUTHORIZATION", sprintf("Bearer %s", $authToken));
        $this->client->request("DELETE", "/rest/users/$id");
        $response = $this->getResponseData();

        $this->assertEquals(Response::HTTP_FORBIDDEN, $response["code"]);
    }


    private function mockListUsers(int $size, int $totalElements) {
        $list = $this->createUserList($totalElements);
        $users = array_slice($list, 0, $size);

        $this->userManager->expects($this->once())->method("list")->with(new UserFilter())->willReturn($users);
        $this->userManager->expects($this->once())->method("countAll")->willReturn($totalElements);

        return $users;
    }


    private function createUserList(int $totalElements): array {
        $users = array ();

        for ($i = 1; $i <= $totalElements; $i++) {
            $user = $this->createUser("user-$i@test.fr", "user-$i-pwd", true);
            $users[] = $user;
        }

        return $users;
    }

}