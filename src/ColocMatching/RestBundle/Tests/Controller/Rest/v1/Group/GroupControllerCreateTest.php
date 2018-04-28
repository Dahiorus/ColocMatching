<?php

namespace ColocMatching\RestBundle\Tests\Controller\Rest\v1\Group;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Manager\Group\GroupDtoManagerInterface;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
use ColocMatching\RestBundle\Tests\AbstractControllerTest;
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
    public function createGroupWithInvalidDataShouldReturn422()
    {
        self::$client->request("POST", "/rest/groups", array (
            "name" => "",
            "budget" => -51,
            "description" => "Description test"
        ));
        self::assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
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
