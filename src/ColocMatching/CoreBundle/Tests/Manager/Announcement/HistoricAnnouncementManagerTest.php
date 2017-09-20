<?php

namespace ColocMatching\CoreBundle\Tests\Manager\Announcement;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Announcement\HistoricAnnouncement;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Exception\HistoricAnnouncementNotFoundException;
use ColocMatching\CoreBundle\Manager\Announcement\HistoricAnnouncementManager;
use ColocMatching\CoreBundle\Manager\Announcement\HistoricAnnouncementManagerInterface;
use ColocMatching\CoreBundle\Repository\Announcement\HistoricAnnouncementRepository;
use ColocMatching\CoreBundle\Repository\Filter\HistoricAnnouncementFilter;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Tests\TestCase;
use ColocMatching\CoreBundle\Tests\Utils\Mock\Announcement\AnnouncementMock;
use ColocMatching\CoreBundle\Tests\Utils\Mock\Announcement\CommentMock;
use ColocMatching\CoreBundle\Tests\Utils\Mock\Announcement\HistoricAnnouncementMock;
use ColocMatching\CoreBundle\Tests\Utils\Mock\User\UserMock;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;

class HistoricAnnouncementManagerTest extends TestCase {

    /**
     * @var HistoricAnnouncementManagerInterface
     */
    private $historicAnnouncementManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $historicAnnouncementRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;


    protected function setUp() {
        $entityClass = "CoreBundle:Announcement\\HistoricAnnouncement";
        $this->historicAnnouncementRepository = $this->createMock(HistoricAnnouncementRepository::class);
        $this->objectManager = $this->createMock(EntityManager::class);
        $this->objectManager->expects($this->once())->method("getRepository")->with($entityClass)->willReturn(
            $this->historicAnnouncementRepository);
        $this->logger = self::getContainer()->get("logger");

        $this->historicAnnouncementManager = new HistoricAnnouncementManager($this->objectManager, $entityClass,
            $this->logger);
    }


    protected function tearDown() {
        $this->logger->info("Test ended.");
    }


    public function testCreate() {
        $this->logger->info("Test creating a historic announcement");

        $announcement = AnnouncementMock::createAnnouncement(1,
            UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL),
            "Paris 75002", "Annoncement test", Announcement::TYPE_SHARING, 589, new \DateTime());
        $expected = new HistoricAnnouncement($announcement);

        $this->objectManager->expects($this->once())->method("persist")->with($expected);

        $actual = $this->historicAnnouncementManager->create($announcement);

        $this->assertNotNull($actual);
        $this->assertEquals($expected, $actual);
    }


    public function testList() {
        $this->logger->info("Test listing historic announcements");

        $filter = new PageableFilter();
        $expected = HistoricAnnouncementMock::createHistoricAnnouncementPage($filter, 30);

        $this->historicAnnouncementRepository->expects($this->once())->method("findByPageable")->with($filter)->willReturn(
            $expected);

        $actual = $this->historicAnnouncementManager->list($filter);

        $this->assertNotNull($actual);
        $this->assertEquals($expected, $actual);
    }


    public function testReadWithSuccess() {
        $this->logger->info("Test reading a historic announcemnt with success");

        $id = 1;
        $announcement = AnnouncementMock::createAnnouncement(1,
            UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL),
            "Paris 75002", "Annoncement test", Announcement::TYPE_SHARING, 589, new \DateTime());
        $expected = HistoricAnnouncementMock::createHistoricAnnouncement($id, $announcement);

        $this->historicAnnouncementRepository->expects($this->once())->method("findById")->with($id)->willReturn(
            $expected);

        $actual = $this->historicAnnouncementManager->read($id);

        $this->assertNotNull($actual);
        $this->assertEquals($expected, $actual);
    }


    public function testReadWithNotFound() {
        $this->logger->info("Test reading a non existing historic announcement");

        $id = 1;

        $this->historicAnnouncementRepository->expects($this->once())->method("findById")->with($id)->willReturn(null);
        $this->expectException(HistoricAnnouncementNotFoundException::class);

        $this->historicAnnouncementManager->read($id);
    }


    public function testSearch() {
        $this->logger->info("Test searching historic announcements");

        $filter = new HistoricAnnouncementFilter();
        $expected = HistoricAnnouncementMock::createHistoricAnnouncementPage($filter, 30);

        $this->historicAnnouncementRepository->expects($this->once())->method("findByFilter")->with($filter)->willReturn(
            $expected);

        $actual = $this->historicAnnouncementManager->search($filter);

        $this->assertNotNull($actual);
        $this->assertEquals($expected, $actual);
    }


    public function testGetComments() {
        $this->logger->info("Test getting comments of a historic announcement with success");

        $announcement = AnnouncementMock::createAnnouncement(1, UserMock::createUser(10, "proposal@test.fr", "password",
            "User", "Test", UserConstants::TYPE_PROPOSAL), "Paris 75002", "Announcement test",
            Announcement::TYPE_SUBLEASE, 200, new \DateTime());
        $announcement->setComments(CommentMock::createComments(13));

        $histAnnouncement = HistoricAnnouncementMock::createHistoricAnnouncement(1, $announcement);

        $filter = new PageableFilter();
        $filter->setSize(5);

        $comments = $this->historicAnnouncementManager->getComments($histAnnouncement, $filter);

        self::assertCount($filter->getSize(), $comments);
    }

}