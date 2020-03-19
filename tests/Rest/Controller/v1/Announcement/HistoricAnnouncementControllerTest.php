<?php

namespace App\Tests\Rest\Controller\v1\Announcement;

use App\Core\DTO\Announcement\HistoricAnnouncementDto;
use App\Core\DTO\User\UserDto;
use App\Core\Entity\Announcement\AnnouncementType;
use App\Core\Manager\Announcement\AnnouncementDtoManagerInterface;
use App\Core\Manager\Announcement\HistoricAnnouncementDtoManagerInterface;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Core\Repository\Filter\Pageable\PageRequest;
use App\Tests\Rest\AbstractControllerTest;
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


    protected function initServices() : void
    {
        $this->announcementManager = self::getService("coloc_matching.core.announcement_dto_manager");
        $this->userManager = self::getService("coloc_matching.core.user_dto_manager");
        $this->historicAnnouncementManager = self::getService("coloc_matching.core.historic_announcement_dto_manager");
    }


    protected function initTestData() : void
    {
        $this->historicAnnouncement = $this->initHistoricAnnouncement();
        /** @var UserDto $user */
        $user = $this->userManager->read($this->historicAnnouncement->getCreatorId());

        self::$client = self::createAuthenticatedClient($user);
    }


    protected function clearData() : void
    {
        $this->historicAnnouncementManager->deleteAll();
        $this->userManager->deleteAll();
    }


    /**
     * @throws \Exception
     */
    private function initHistoricAnnouncement() : HistoricAnnouncementDto
    {
        $creator = $this->createProposalUser($this->userManager, "creator@test.fr");
        $announcement = $this->announcementManager->create($creator, array (
            "title" => "Announcement test",
            "type" => AnnouncementType::RENT,
            "rentPrice" => 840,
            "startDate" => "2018-12-10",
            "location" => "rue Edouard Colonne, Paris 75001"
        ));

        for ($i = 1; $i <= 5; $i++)
        {
            $author = $this->createSearchUser($this->userManager, "author-$i@test.fr");
            $comment = $this->announcementManager->createComment($announcement, $author, array (
                "message" => "Comment $i",
                "rate" => rand(0, 5)
            ));
            self::assertNotNull($comment, "Expected comment to be created");
        }

        $comments = $this->announcementManager->getComments($announcement, new PageRequest());
        self::assertNotEmpty($comments, "Expected announcement to have comments");

        self::$client = self::createAuthenticatedClient($creator);
        self::$client->request("DELETE", "/rest/announcements/" . $announcement->getId());

        /** @var HistoricAnnouncementDto[] $historicAnnouncements */
        $historicAnnouncements = $this->historicAnnouncementManager->list()->getContent();
        self::assertNotEmpty($historicAnnouncements, "Expected to find historic announcements");

        return $historicAnnouncements[0];
    }


    /**
     * @test
     */
    public function getSelfHistoricAnnouncementsShouldReturn200()
    {
        self::$client->request("GET", "/rest/history/announcements");
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function getSelfHistoricAnnouncementsAsAnonymousShouldReturn401()
    {
        self::$client = self::initClient();
        self::$client->request("GET", "/rest/history/announcements");
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
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
    public function getHistoricAnnouncementCommentsShouldReturn200()
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
