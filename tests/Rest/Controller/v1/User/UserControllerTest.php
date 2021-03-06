<?php

namespace App\Tests\Rest\Controller\v1\User;

use App\Core\DTO\User\UserDto;
use App\Core\Entity\Announcement\AnnouncementType;
use App\Core\Manager\Announcement\AnnouncementDtoManagerInterface;
use App\Core\Manager\Group\GroupDtoManagerInterface;
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
        $this->userTest = $this->createProposalUser($this->userManager, "user@test.fr");
    }


    protected function clearData() : void
    {
        $this->userManager->deleteAll();
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


    /**
     * @test
     */
    public function getAnnouncementsShouldReturn200()
    {
        self::$client = self::initClient();
        self::$client->request("GET", "/rest/users/" . $this->userTest->getId() . "/announcements");
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getAnnouncementsAsProposalUserShouldReturn200()
    {
        $user = $this->createProposalUser($this->userManager, "proposal@yopmail.com");
        /** @var AnnouncementDtoManagerInterface $announcementManager */
        $announcementManager = self::getService("coloc_matching.core.announcement_dto_manager");
        $announcementManager->create($user, array (
            "title" => "my announcement",
            "location" => "Paris 19",
            "rentPrice" => 1200,
            "startDate" => "2019-01-10",
            "type" => AnnouncementType::RENT
        ));
        $userId = $user->getId();

        self::$client = self::initClient();
        self::$client->request("GET", "/rest/users/$userId/announcements");
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getGroupsAsProposalUserShouldReturn200()
    {
        self::$client = self::createAuthenticatedClient($this->userTest);
        self::$client->request("GET", "/rest/users/" . $this->userTest->getId() . "/groups");
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getGroupsAsSearchUserShouldReturn200()
    {
        $user = $this->createSearchUser($this->userManager, "search@yopmail.com");
        /** @var GroupDtoManagerInterface $groupManager */
        $groupManager = self::getService("coloc_matching.core.group_dto_manager");
        $groupManager->create($user, array (
            "name" => "my group",
        ));
        $userId = $user->getId();

        self::$client = self::createAuthenticatedClient($user);
        self::$client->request("GET", "/rest/users/$userId/groups");
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function getGroupsAsAnonymousShouldReturn401()
    {
        self::$client = self::initClient();
        self::$client->request("GET", "/rest/users/" . $this->userTest->getId() . "/groups");
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }

}
