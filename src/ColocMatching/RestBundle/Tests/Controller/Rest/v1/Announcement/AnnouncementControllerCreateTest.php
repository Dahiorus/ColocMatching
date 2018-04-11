<?php

namespace ColocMatching\RestBundle\Tests\Controller\Rest\v1\Announcement;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Manager\Announcement\AnnouncementDtoManagerInterface;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
use ColocMatching\RestBundle\Tests\AbstractControllerTest;
use Symfony\Component\HttpFoundation\Response;

class AnnouncementControllerCreateTest extends AbstractControllerTest
{
    /** @var UserDtoManagerInterface */
    private $userManager;

    /** @var UserDto */
    private $creatorTest;


    /**
     * @throws \Exception
     */
    protected function setUp()
    {
        parent::setUp();
        $this->userManager = self::getService("coloc_matching.core.user_dto_manager");
        $this->creatorTest = $this->userManager->create(array (
            "email" => "user@test.fr",
            "plainPassword" => "Secret1234&",
            "firstName" => "User",
            "lastName" => "Test",
            "type" => UserConstants::TYPE_PROPOSAL
        ));
        self::assertNotNull($this->creatorTest, "Expected user to be created");

        self::$client = self::createAuthenticatedClient($this->creatorTest);
    }


    protected function tearDown()
    {
        /** @var AnnouncementDtoManagerInterface $announcementManager */
        $announcementManager = self::getService("coloc_matching.core.announcement_dto_manager");
        $announcementManager->deleteAll();
        $this->userManager->deleteAll();

        parent::tearDown();
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
    public function createAnnouncementWithInvalidDataShouldReturn422()
    {
        $data = array (
            "title" => "",
            "type" => Announcement::TYPE_RENT,
            "rentPrice" => -840,
            "startDate" => "2018-12-10",
            "location" => null
        );

        self::$client->request("POST", "/rest/announcements", $data);
        self::assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
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