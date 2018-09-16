<?php

namespace App\Tests\Rest\Controller\v1\Announcement;

use App\Core\DTO\Announcement\AnnouncementDto;
use App\Core\DTO\User\UserDto;
use App\Core\Entity\Announcement\Announcement;
use App\Core\Entity\Announcement\Housing;
use App\Core\Entity\User\UserType;
use App\Core\Manager\Announcement\AnnouncementDtoManagerInterface;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Tests\Rest\AbstractControllerTest;
use Symfony\Component\HttpFoundation\Response;

class HousingControllerTest extends AbstractControllerTest
{
    /** @var AnnouncementDtoManagerInterface */
    private $announcementManager;

    /** @var UserDtoManagerInterface */
    private $userManager;

    /** @var AnnouncementDto */
    private $announcement;

    /** @var UserDto */
    private $creator;


    protected function initServices() : void
    {
        $this->announcementManager = self::getService("coloc_matching.core.announcement_dto_manager");
        $this->userManager = self::getService("coloc_matching.core.user_dto_manager");
    }


    protected function initTestData() : void
    {
        $this->announcement = $this->createAnnouncement();
        self::$client = self::createAuthenticatedClient($this->creator);
    }


    protected function clearData() : void
    {
        $this->announcementManager->deleteAll();
        $this->userManager->deleteAll();
    }


    /**
     * @return AnnouncementDto
     * @throws \Exception
     */
    private function createAnnouncement() : AnnouncementDto
    {
        $this->creator = $this->userManager->create(array (
            "email" => "user@test.fr",
            "plainPassword" => "Secret1234&",
            "firstName" => "User",
            "lastName" => "Test",
            "type" => UserType::PROPOSAL
        ));

        return $this->announcementManager->create($this->creator, array (
            "title" => "Announcement test",
            "type" => Announcement::TYPE_RENT,
            "rentPrice" => 840,
            "startDate" => "2018-12-10",
            "location" => "rue Edouard Colonne, Paris 75001"
        ));
    }


    /**
     * @test
     */
    public function getAnnouncementHousingShouldReturn200()
    {
        self::$client->request("GET", "/rest/announcements/" . $this->announcement->getId() . "/housing");
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function getNonExistingAnnouncementHousingShouldReturn404()
    {
        self::$client->request("GET", "/rest/announcements/0/housing");
        self::assertStatusCode(Response::HTTP_NOT_FOUND);
    }


    /**
     * @test
     */
    public function getAnnouncementHousingAsAnonymousShouldReturn200()
    {
        self::$client = self::initClient();

        self::$client->request("GET", "/rest/announcements/" . $this->announcement->getId() . "/housing");
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function putAnnouncementHousingShouldReturn200()
    {
        self::$client->request("PUT", "/rest/announcements/" . $this->announcement->getId() . "/housing", array (
            "type" => Housing::TYPE_HOUSE,
            "roomCount" => 5,
            "bedroomCount" => 2,
            "bathroomCount" => 1,
            "surfaceArea" => 45,
            "roomMateCount" => 1
        ));
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function putAnnouncementHousingWithInvalidDataShouldReturn400()
    {
        self::$client->request("PUT", "/rest/announcements/" . $this->announcement->getId() . "/housing", array (
            "type" => "",
            "roomCount" => -5,
            "bedroomCount" => 2,
            "bathRoomCount" => 1,
            "surfaceArea" => -45,
            "roomMateCount" => null
        ));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }


    /**
     * @test
     */
    public function putNonExistingAnnouncementHousingShouldReturn404()
    {
        self::$client->request("PUT", "/rest/announcements/0/housing", array (
            "bedroomCount" => 2
        ));
        self::assertStatusCode(Response::HTTP_NOT_FOUND);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function putAnnouncementHousingAsNonCreatorShouldReturn403()
    {
        $user = $this->userManager->create(array (
            "email" => "visitor@test.fr",
            "plainPassword" => "Secret1234&",
            "firstName" => "Visitor",
            "lastName" => "Test",
            "type" => UserType::PROPOSAL
        ));
        self::$client = self::createAuthenticatedClient($user);

        self::$client->request("PUT", "/rest/announcements/" . $this->announcement->getId() . "/housing", array (
            "type" => Housing::TYPE_APARTMENT,
            "bedroomCount" => 2
        ));
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }


    /**
     * @test
     */
    public function patchAnnouncementHousingShouldReturn200()
    {
        self::$client->request("PATCH", "/rest/announcements/" . $this->announcement->getId() . "/housing", array (
            "bedroomCount" => 2
        ));
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function patchAnnouncementHousingWithInvalidDataShouldReturn400()
    {
        self::$client->request("PATCH", "/rest/announcements/" . $this->announcement->getId() . "/housing", array (
            "bedroomCount" => 2,
            "unknown" => "test"
        ));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }


    /**
     * @test
     */
    public function patchNonExistingAnnouncementHousingShouldReturn404()
    {
        self::$client->request("PATCH", "/rest/announcements/0/housing", array (
            "bedroomCount" => 2
        ));
        self::assertStatusCode(Response::HTTP_NOT_FOUND);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function patchAnnouncementHousingAsNonCreatorShouldReturn403()
    {
        $user = $this->userManager->create(array (
            "email" => "visitor@test.fr",
            "plainPassword" => "Secret1234&",
            "firstName" => "Visitor",
            "lastName" => "Test",
            "type" => UserType::PROPOSAL
        ));
        self::$client = self::createAuthenticatedClient($user);

        self::$client->request("PATCH", "/rest/announcements/" . $this->announcement->getId() . "/housing", array ());
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }

}
