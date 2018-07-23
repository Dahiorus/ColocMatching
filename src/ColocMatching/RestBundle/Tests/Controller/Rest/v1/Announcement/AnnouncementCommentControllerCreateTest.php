<?php

namespace ColocMatching\RestBundle\Tests\Controller\Rest\v1\Announcement;

use ColocMatching\CoreBundle\DTO\Announcement\AnnouncementDto;
use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Manager\Announcement\AnnouncementDtoManagerInterface;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
use ColocMatching\RestBundle\Tests\AbstractControllerTest;
use Symfony\Component\HttpFoundation\Response;

class AnnouncementCommentControllerCreateTest extends AbstractControllerTest
{
    /** @var AnnouncementDtoManagerInterface */
    private $announcementManager;

    /** @var UserDtoManagerInterface */
    private $userManager;

    /** @var AnnouncementDto */
    private $announcement;

    /** @var UserDto */
    private $author;


    protected function initServices() : void
    {
        $this->announcementManager = self::getService("coloc_matching.core.announcement_dto_manager");
        $this->userManager = self::getService("coloc_matching.core.user_dto_manager");
    }


    protected function initTestData() : void
    {
        $this->announcement = $this->createAnnouncement();
        $this->createAuthor();

        self::$client = self::createAuthenticatedClient($this->author);
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
        $creator = $this->userManager->create(array (
            "email" => "user@test.fr",
            "plainPassword" => "Secret1234&",
            "firstName" => "User",
            "lastName" => "Test",
            "type" => UserConstants::TYPE_PROPOSAL
        ));

        return $this->announcementManager->create($creator, array (
            "title" => "Announcement test",
            "type" => Announcement::TYPE_RENT,
            "rentPrice" => 840,
            "startDate" => "2018-12-10",
            "location" => "rue Edouard Colonne, Paris 75001"
        ));
    }


    /**
     * @throws \Exception
     */
    private function createAuthor()
    {
        $this->author = $this->userManager->create(array (
            "email" => "author@test.fr",
            "plainPassword" => "Secret1234&",
            "firstName" => "Author",
            "lastName" => "Test",
            "type" => UserConstants::TYPE_SEARCH
        ));
        $this->announcementManager->addCandidate($this->announcement, $this->author);
        self::assertCount(1, $this->announcementManager->getCandidates($this->announcement),
            "Expected announcement to have 1 candidate");
    }


    /**
     * @test
     */
    public function createCommentShouldReturn200()
    {
        self::$client->request("POST", "/rest/announcements/" . $this->announcement->getId() . "/comments", array (
            "message" => "Comment message",
            "rate" => 3
        ));
        self::assertStatusCode(Response::HTTP_CREATED);
    }


    /**
     * @test
     */
    public function createCommentWithInvalidDataShouldReturn400()
    {
        self::$client->request("POST", "/rest/announcements/" . $this->announcement->getId() . "/comments", array (
            "message" => "",
            "rate" => -3
        ));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }


    /**
     * @test
     */
    public function createCommentAsAnonymousShouldReturn401()
    {
        self::$client = self::initClient();
        self::$client->request("POST", "/rest/announcements/" . $this->announcement->getId() . "/comments", array (
            "message" => "Comment message",
            "rate" => 3
        ));
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function createCommentAsNonCandidateShouldReturn403()
    {
        /** @var UserDto $creator */
        $creator = $this->userManager->read($this->announcement->getCreatorId());
        self::$client = self::createAuthenticatedClient($creator);
        self::$client->request("POST", "/rest/announcements/" . $this->announcement->getId() . "/comments", array (
            "message" => "Comment message",
            "rate" => 3
        ));
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }
}