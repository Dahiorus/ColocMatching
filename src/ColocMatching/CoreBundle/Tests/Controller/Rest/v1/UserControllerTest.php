<?php

namespace ColocMatching\CoreBundle\Tests\Controller\Rest\v1;

use Symfony\Component\HttpFoundation\Response;
use ColocMatching\CoreBundle\Repository\Filter\UserFilter;

class UserControllerTest extends AuthenticatedTestCase {


    public function testGetUsersActionWith200() {
        $this->logger->info("Test get users with status code 200");

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
        $this->logger->info("Test get users with status code 206");

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


    private function createUserList(int $totalElements): array {
        $users = array ();

        for ($i = 1; $i <= $totalElements; $i++) {
            $user = $this->createUser("user-$i@test.fr", "user-$i-pwd", true);
            $users[] = $user;
        }

        return $users;
    }

}