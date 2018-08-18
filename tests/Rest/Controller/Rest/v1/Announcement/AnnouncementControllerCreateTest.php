<?php

namespace App\Tests\Rest\Controller\Rest\v1\Announcement;

use App\Core\DTO\User\UserDto;
use App\Core\Entity\Announcement\Announcement;
use App\Core\Entity\User\UserConstants;
use App\Core\Manager\Announcement\AnnouncementDtoManagerInterface;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Tests\Rest\AbstractControllerTest;
use Symfony\Component\HttpFoundation\Response;

class AnnouncementControllerCreateTest extends AbstractControllerTest
{
    /** @var UserDtoManagerInterface */
    private $userManager;

    /** @var UserDto */
    private $creatorTest;


    protected function initServices() : void
    {
        $this->userManager = self::getService("coloc_matching.core.user_dto_manager");
    }


    protected function initTestData() : void
    {
        $this->creatorTest = $this->userManager->create(array (
            "email" => "user@test.fr",
            "plainPassword" => "Secret1234&",
            "firstName" => "User",
            "lastName" => "Test",
            "type" => UserConstants::TYPE_PROPOSAL
        ));

        self::$client = self::createAuthenticatedClient($this->creatorTest);
    }


    protected function clearData() : void
    {
        /** @var AnnouncementDtoManagerInterface $announcementManager */
        $announcementManager = self::getService("coloc_matching.core.announcement_dto_manager");
        $announcementManager->deleteAll();
        $this->userManager->deleteAll();
    }


    /**
     * @test
     */
    public function createAnnouncementShouldReturn201()
    {
        $data = array (
            "title" => "Announcement test",
            "type" => Announcement::TYPE_RENT,
            "rentPrice" => 840,
            "startDate" => "2018-12-10",
            "location" => "rue Edouard Colonne, Paris 75001"
        );

        self::$client->request("POST", "/rest/announcements", $data);
        self::assertStatusCode(Response::HTTP_CREATED);
        self::assertHasLocation();
    }


    /**
     * @test
     */
    public function createAnnouncementWithInvalidDataShouldReturn400()
    {
        $data = array (
            "title" => "",
            "type" => Announcement::TYPE_RENT,
            "rentPrice" => -840,
            "startDate" => "2018-12-10",
            "location" => null
        );

        self::$client->request("POST", "/rest/announcements", $data);
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function createAnnouncementAsNonProposalShouldReturn403()
    {
        $this->creatorTest = $this->userManager->update($this->creatorTest,
            array ("type" => UserConstants::TYPE_SEARCH), false);
        self::$client = self::createAuthenticatedClient($this->creatorTest);

        $data = array (
            "title" => "Announcement test",
            "type" => Announcement::TYPE_RENT,
            "rentPrice" => 840,
            "startDate" => "2018-12-10",
            "location" => "rue Edouard Colonne, Paris 75001"
        );

        self::$client->request("POST", "/rest/announcements", $data);
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }


    /**
     * @test
     */
    public function createAnnouncementAsAnonymousShouldReturn401()
    {
        self::$client = self::initClient();

        self::$client->request("POST", "/rest/announcements", array ());
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }

}