<?php

namespace ColocMatching\CoreBundle\Tests\Controller\Rest\v1;

use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Form\Type\User\UserType;
use ColocMatching\CoreBundle\Repository\Filter\UserFilter;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends AuthenticatedTestCase {


    public function testGetUsersActionWith200() {
        $this->logger->info("Test getting users with status code 200");

        $size = 20;
        $users = $this->createUserList($size);
        $this->userManager->expects($this->once())->method("list")->with(new UserFilter())->willReturn(
            array_slice($users, 0, $size));
        $this->userManager->expects($this->once())->method("countAll")->willReturn($size);

        $this->client->request("GET", "/rest/users/");

        /** @var Response */
        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $restList = json_decode($response->getContent(), true);
        $this->assertEquals($size, $restList["size"],
            sprintf("Expected to get an array of %d elements, but got %d", $size, $restList["size"]));
        $this->assertEquals($size, $restList["total"],
            sprintf("Expected total elements to equal to %d, but got %d", $size, $restList["total"]));
    }


    public function testGetUsersActionWith206() {
        $this->logger->info("Test getting users with status code 206");

        $size = 20;
        $total = 30;
        $users = $this->createUserList($total);
        $this->userManager->expects($this->once())->method("list")->with(new UserFilter())->willReturn(
            array_slice($users, 0, $size));
        $this->userManager->expects($this->once())->method("countAll")->willReturn($total);

        $this->client->request("GET", "/rest/users/");

        /** @var Response */
        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_PARTIAL_CONTENT, $response->getStatusCode());

        $restList = json_decode($response->getContent(), true);
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

        /** @var Response */
        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        $restData = json_decode($response->getContent(), true);
        $createdUser = $restData["data"];
        $this->assertEquals($createdUser["email"], $data["email"],
            sprintf("Expected user email to be equal to '%s', but got '%s'", $data["email"], $createdUser["email"]));
        $this->assertEquals($createdUser["firstname"], $data["firstname"],
            sprintf("Expected user firstname to be equal to '%s', but got '%s'", $data["firstname"],
                $createdUser["firstname"]));
    }


    public function testCreateUserActionWith400() {
        $this->logger->info("Test creating user with status code 400");

        $data = array ("email" => "");
        $form = $this->client->getKernel()->getContainer()->get("form.factory")->create(UserType::class);
        $this->userManager->expects($this->once())->method("create")->with($data)->willThrowException(
            new InvalidFormDataException("Invalid data submitted in the user form", $form->getErrors(true, true)));

        $this->client->request("POST", "/rest/users/", $data);

        /** @var Response */
        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
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