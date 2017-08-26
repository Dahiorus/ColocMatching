<?php

namespace ColocMatching\CoreBundle\Tests\Controller\Rest\v1\Visit;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Exception\AnnouncementNotFoundException;
use ColocMatching\CoreBundle\Exception\VisitNotFoundException;
use ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManager;
use ColocMatching\CoreBundle\Manager\Visit\VisitManager;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Repository\Filter\VisitFilter;
use ColocMatching\CoreBundle\Tests\Controller\Rest\v1\RestTestCase;
use ColocMatching\CoreBundle\Tests\Utils\Mock\Announcement\AnnouncementMock;
use ColocMatching\CoreBundle\Tests\Utils\Mock\User\UserMock;
use ColocMatching\CoreBundle\Tests\Utils\Mock\Visit\VisitMock;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

class AnnouncementVisitControllerTest extends RestTestCase {

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $visitManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $announcementManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var User
     */
    private $authenticatedUser;


    protected function setUp() {
        parent::setUp();

        $this->visitManager = $this->createMock(VisitManager::class);
        $this->client->getContainer()->set("coloc_matching.core.announcement_visit_manager", $this->visitManager);

        $this->announcementManager = $this->createMock(AnnouncementManager::class);
        $this->client->getContainer()->set("coloc_matching.core.announcement_manager", $this->announcementManager);

        $this->logger = $this->client->getContainer()->get("logger");

        $this->authenticatedUser = UserMock::createUser(1, "user@test.fr", "password", "User", "Test",
            UserConstants::TYPE_PROPOSAL);
        $this->setAuthenticatedRequest($this->authenticatedUser);
    }


    protected function tearDown() {
        $this->logger->info("End test");
    }


    public function testGetVisitsActionWith200() {
        $this->logger->info("Test getting visits of one announcement with status code 200");

        $total = 30;
        $filter = new PageableFilter();
        $filter->setPage(2);
        $announcement = AnnouncementMock::createAnnouncement(1, $this->authenticatedUser, "Paris 75006",
            "Announcement in test", Announcement::TYPE_RENT, 1430, new \DateTime());
        $visits = VisitMock::createVisitPageForVisited($filter, $total, $announcement);

        $this->announcementManager->expects(self::once())->method("read")->with($announcement->getId())
            ->willReturn($announcement);
        $this->visitManager->expects(self::once())->method("listByVisited")->with($announcement,
            $filter)->willReturn($visits);
        $this->visitManager->expects(self::once())->method("countByVisited")->willReturn($total);

        $this->client->request("GET", "/rest/announcements/1/visits", array ("page" => 2));
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_OK, $response["code"]);
        self::assertCount(count($visits), $response["rest"]["content"]);
        self::assertEquals($filter->getSize(), $response["rest"]["size"]);
    }


    public function testGetVisitsActionWith206() {
        $this->logger->info("Test getting visits of one announcement with status code 206");

        $total = 30;
        $filter = new PageableFilter();
        $filter->setPage(1);
        $announcement = AnnouncementMock::createAnnouncement(1, $this->authenticatedUser, "Paris 75006",
            "Announcement in test", Announcement::TYPE_RENT, 1430, new \DateTime());
        $visits = VisitMock::createVisitPageForVisited($filter, $total, $announcement);

        $this->announcementManager->expects(self::once())->method("read")->with($announcement->getId())->willReturn($announcement);
        $this->visitManager->expects(self::once())->method("listByVisited")->with($announcement,
            $filter)->willReturn($visits);
        $this->visitManager->expects(self::once())->method("countByVisited")->willReturn($total);

        $this->client->request("GET", "/rest/announcements/1/visits");
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_PARTIAL_CONTENT, $response["code"]);
        self::assertCount(count($visits), $response["rest"]["content"]);
    }


    public function testGetVisitsActionWith404() {
        $this->logger->info("Test getting visits of one announcement with status code 404");

        $this->announcementManager->expects(self::once())->method("read")->with(1)
            ->willThrowException(new AnnouncementNotFoundException("id", 1));
        $this->visitManager->expects($this->never())->method("listByVisited");
        $this->visitManager->expects($this->never())->method("countAll");

        $this->client->request("GET", "/rest/announcements/1/visits");
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testGetVisitActionWith200() {
        $this->logger->info("Test getting one visit on one announcement with status code 200");

        $id = 1;
        $expectedVisit = VisitMock::createVisit(
            $id,
            AnnouncementMock::createAnnouncement(1,
                UserMock::createUser(1, "owner@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL),
                "Paris 75004", "Announcement in test", Announcement::TYPE_SHARING, 1570, new \DateTime()),
            $this->authenticatedUser, new \DateTime());

        $this->announcementManager->expects(self::once())->method("read")->with(1)->willReturn($expectedVisit->getVisited());
        $this->visitManager->expects(self::once())->method("read")->with($id)->willReturn($expectedVisit);

        $this->client->request("GET", "/rest/announcements/1/visits/$id");
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_OK, $response["code"]);
    }


    public function testGetVisitActionWith404() {
        $this->logger->info("Test getting one visit on one announcement with status code 404");

        $id = 1;

        $this->visitManager->expects(self::once())->method("read")->with($id)->willThrowException(
            new VisitNotFoundException("id", $id));

        $this->client->request("GET", "/rest/announcements/1/visits/$id");
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testSearchVisitsWith200() {
        $this->logger->info("Test searching announcements visits with status code 200");

        $total = 30;
        $filter = new VisitFilter();
        $filter->setPage(2);
        $filter->setVisitedId(1);
        $announcement = AnnouncementMock::createAnnouncement(1, $this->authenticatedUser, "Paris 75006",
            "Announcement in test", Announcement::TYPE_RENT, 1430, new \DateTime());
        $visits = VisitMock::createVisitPageForVisited($filter, $total, $announcement);

        $this->visitManager->expects(self::once())->method("search")->with($filter)->willReturn($visits);
        $this->visitManager->expects(self::once())->method("countBy")->with($filter)->willReturn($total);

        $this->client->request("POST", "/rest/announcements/1/visits/searches", array ("page" => 2));
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_OK, $response["code"]);
        self::assertNotNull($response["rest"]);
    }


    public function testSearchAnnouncementsVisitsWith206() {
        $this->logger->info("Test searching announcements visits with status code 206");

        $total = 30;
        $filter = new VisitFilter();
        $filter->setVisitedId(1);
        $announcement = AnnouncementMock::createAnnouncement(1, $this->authenticatedUser, "Paris 75006",
            "Announcement in test", Announcement::TYPE_RENT, 1430, new \DateTime());
        $visits = VisitMock::createVisitPageForVisited($filter, $total, $announcement);

        $this->visitManager->expects(self::once())->method("search")->with($filter)->willReturn($visits);
        $this->visitManager->expects(self::once())->method("countBy")->with($filter)->willReturn($total);

        $this->client->request("POST", "/rest/announcements/1/visits/searches");
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_PARTIAL_CONTENT, $response["code"]);
        self::assertNotNull($response["rest"]);
    }

}