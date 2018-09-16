<?php

namespace App\Tests\Rest\Controller\v1\User;

use App\Core\DTO\User\UserDto;
use App\Core\Entity\User\UserType;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Tests\Rest\AbstractControllerTest;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends AbstractControllerTest
{
    /** @var UserDtoManagerInterface */
    private $userManager;

    /** @var UserDto */
    private $userTest;


    protected function initServices() : void
    {
        $this->userManager = self::getService("coloc_matching.core.user_dto_manager");
    }


    protected function initTestData() : void
    {
        $this->userTest = $this->createUser();
    }


    protected function clearData() : void
    {
        $this->userManager->deleteAll();
    }


    /**
     * @return UserDto
     * @throws \Exception
     */
    private function createUser() : UserDto
    {
        return $this->userManager->create(array (
            "email" => "user@test.fr",
            "plainPassword" => "Secret1234&",
            "firstName" => "User",
            "lastName" => "Test",
            "type" => UserType::SEARCH
        ));
    }


    /**
     * @test
     */
    public function getUserShouldReturn200()
    {
        self::$client = self::initClient();
        self::$client->request("GET", "/rest/users/" . $this->userTest->getId());
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function getNonExistingUserShouldReturn404()
    {
        self::$client = self::initClient();
        self::$client->request("GET", "/rest/users/0");
        self::assertStatusCode(Response::HTTP_NOT_FOUND);
    }

}
