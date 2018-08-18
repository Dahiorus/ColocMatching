<?php

namespace App\Tests\Rest\Controller\v1\Group;

use App\Core\DTO\User\UserDto;
use App\Core\Entity\User\UserConstants;
use App\Core\Manager\Group\GroupDtoManagerInterface;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Tests\Rest\AbstractControllerTest;
use Symfony\Component\HttpFoundation\Response;

class GroupControllerCreateTest extends AbstractControllerTest
{
    /** @var UserDtoManagerInterface */
    private $userManager;

    /** @var UserDto */
    private $user;


    protected function initServices() : void
    {
        $this->userManager = self::getService("coloc_matching.core.user_dto_manager");
    }


    protected function initTestData() : void
    {
        $this->user = $this->userManager->create(array (
            "email" => "user@test.fr",
            "plainPassword" => "Secret1234&",
            "firstName" => "User",
            "lastName" => "Test",
            "type" => UserConstants::TYPE_SEARCH
        ));
        self::$client = self::createAuthenticatedClient($this->user);
    }


    protected function clearData() : void
    {
        /** @var GroupDtoManagerInterface $groupManager */
        $groupManager = self::getService("coloc_matching.core.group_dto_manager");
        $groupManager->deleteAll();
        $this->userManager->deleteAll();
    }


    /**
     * @test
     */
    public function createGroupShouldReturn201()
    {
        self::$client->request("POST", "/rest/groups", array (
            "name" => "Group test",
            "budget" => 599,
            "description" => "Description test"
        ));
        self::assertStatusCode(Response::HTTP_CREATED);
        self::assertHasLocation();
    }


    /**
     * @test
     */
    public function createGroupWithInvalidDataShouldReturn400()
    {
        self::$client->request("POST", "/rest/groups", array (
            "name" => "",
            "budget" => -51,
            "description" => "Description test"
        ));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function createGroupAsNonSearchUserShouldReturn403()
    {
        $this->user = $this->userManager->update($this->user, array ("type" => UserConstants::TYPE_PROPOSAL), false);
        self::$client = self::createAuthenticatedClient($this->user);

        self::$client->request("POST", "/rest/groups", array (
            "name" => "Group test",
            "budget" => 599,
            "description" => "Description test"
        ));
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }


    /**
     * @test
     */
    public function createGroupAsAnonymousShouldReturn401()
    {
        self::$client = self::initClient();
        self::$client->request("POST", "/rest/groups", array (
            "name" => "Group test",
            "budget" => 599,
            "description" => "Description test"
        ));
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }

}
