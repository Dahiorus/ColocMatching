<?php

namespace ColocMatching\RestBundle\Tests\Controller\Rest\v1\Announcement;

use ColocMatching\CoreBundle\DTO\Announcement\HistoricAnnouncementDto;
use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Manager\Announcement\AnnouncementDtoManagerInterface;
use ColocMatching\CoreBundle\Manager\Announcement\HistoricAnnouncementDtoManagerInterface;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\RestBundle\Tests\AbstractControllerTest;
use Symfony\Component\HttpFoundation\Response;

class HistoricAnnouncementControllerTest extends AbstractControllerTest
{
    /** @var AnnouncementDtoManagerInterface */
    private $announcementManager;

    /** @var UserDtoManagerInterface */
    private $userManager;

    /** @var HistoricAnnouncementDtoManagerInterface */
    private $historicAnnouncementManager;

    /** @var HistoricAnnouncementDto */
    private $historicAnnouncement;


    /**
     * @throws \Exception
     */
    protected function setUp()
    {
        parent::setUp();

        $this->announcementManager = self::getService("coloc_matching.core.announcement_dto_manager");
        $this->userManager = self::getService("coloc_matching.core.user_dto_manager");
        $this->historicAnnouncementManager = self::getService("coloc_matching.core.historic_announcement_dto_manager");

        $this->historicAnnouncement = $this->initHistoricAnnouncement();
        /** @var UserDto $user */
        $user = $this->userManager->read($this->historicAnnouncement->getCreatorId());

        self::$client = self::createAuthenticatedClient($user);
    }


    protected function tearDown()
    {
        $this->historicAnnouncementManager->deleteAll(false);
        $this->announcementManager->deleteAll(false);
        $this->userManager->deleteAll(true);

        parent::tearDown();
    }


    /**
     * @throws \Exception
     */
    private function initHistoricAnnouncement() : HistoricAnnouncementDto
    {
        $creator = $this->userManager->create(array (
            "email" => "creator@test.fr",
            "plainPassword" => "Secret1234&",
            "firstName" => "User",
            "lastName" => "Test",
            "type" => UserConstants::TYPE_PROPOSAL
        ));
        $announcement = $this->announcementManager->create($creator, array (
            "title" => "Announcement test",
            "type" => Announcement::TYPE_RENT,
            "rentPrice" => 840,
            "startDate" => "2018-12-10",
            "location" => "rue Edouard Colonne, Paris 75001"
        ));

        for ($i = 1; $i <= 8; $i++)
        {
            $author = $this->userManager->create(array (
                "email" => "author-$i@test.fr",
                "plainPassword" => "Secret1234&",
                "firstName" => "User-$i",
                "lastName" => "Test",
                "type" => UserConstants::TYPE_SEARCH
            ));
            $comment = $this->announcementManager->createComment($announcement, $author, array (
                "message" => "Comment $i",
                "rate" => rand(0, 5)
            ));
            self::assertNotNull($comment, "Expected comment to be created");
        }

        $comments = $this->announcementManager->getComments($announcement, new PageableFilter());
        self::assertNotEmpty($comments, "Expected announcement to have comments");

        self::$client = self::createAuthenticatedClient($creator);
        self::$client->request("DELETE", "/rest/announcements/" . $announcement->getId());

        /** @var HistoricAnnouncementDto[] $historicAnnouncements */
        $historicAnnouncements = $this->historicAnnouncementManager->findAll();
        self::assertNotEmpty($historicAnnouncements, "Expected to find historic announcements");

        return $historicAnnouncements[0];
    }


    /**
     * @test
     */
    public function getHistoricAnnouncementShouldReturn200()
    {
        self::$client->request("GET", "/rest/history/announcements/" . $this->historicAnnouncement->getId());
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function getNonExistingHistoricAnnouncementShouldReturn404()
    {
        self::$client->request("GET", "/rest/history/announcements/0");
        self::assertStatusCode(Response::HTTP_NOT_FOUND);
    }


    /**
     * @test
     */
    public function getHistoricAnnouncementAsAnonymousShouldReturn401()
    {
        self::$client = self::initClient();
        self::$client->request("GET", "/rest/history/announcements/" . $this->historicAnnouncement->getId());
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }


    /**
     * @test
     */
    public function getSomeHistoricAnnouncementCommentsShouldReturn206()
    {
        self::$client->request("GET",
            "/rest/history/announcements/" . $this->historicAnnouncement->getId() . "/comments", array ("size" => 4));
        self::assertStatusCode(Response::HTTP_PARTIAL_CONTENT);
    }


    /**
     * @test
     */
    public function getAllHistoricAnnouncementCommentsShouldReturn200()
    {
        self::$client->request("GET",
            "/rest/history/announcements/" . $this->historicAnnouncement->getId() . "/comments");
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function getNonExistingHistoricAnnouncementCommentsShouldReturn404()
    {
        self::$client->request("GET",
            "/rest/history/announcements/0/comments");
        self::assertStatusCode(Response::HTTP_NOT_FOUND);
    }


    /**
     * @test
     */
    public function getHistoricAnnouncementCommentsAsAnonymousShouldReturn401()
    {
        self::$client = self::initClient();
        self::$client->request("GET",
            "/rest/history/announcements/" . $this->historicAnnouncement->getId() . "/comments");
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }
}