<?php

namespace ColocMatching\CoreBundle\Tests\Manager\Announcement;

use ColocMatching\CoreBundle\Tests\TestCase;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManager;
use ColocMatching\CoreBundle\Manager\User\UserManager;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use ColocMatching\CoreBundle\Repository\Filter\AnnouncementFilter;
use ColocMatching\CoreBundle\Exception\AnnouncementNotFoundException;

class AnnouncementManagerTest extends TestCase {

    /**
     * @var AnnouncementManager
     */
    private $announcementManager;

    /**
     * @var UserManager
     */
    private $userManager;

    private $dateFormat = "d/m/Y";


    protected function setUp() {
        $this->announcementManager = self::getContainer()->get("coloc_matching.core.announcement_manager");
        $this->userManager = self::getContainer()->get("coloc_matching.core.user_manager");
    }


    protected function tearDown() {
    }


    public function testCreateAnnouncement() {
        self::$logger->info("Test creating an announcement");

        $data = array (
            "title" => "Announcement test",
            "type" => Announcement::TYPE_RENT,
            "minPrice" => 520,
            "description" => "Announcement created from unit test",
            "startDate" => "05/03/2017",
            "location" => "Paris");
        /** @var User */
        $creator = $this->userManager->read(4);

        /** @var Announcement */
        $announcement = $this->announcementManager->create($creator, $data);

        $this->assertNotNull($announcement);
        $this->assertEquals($creator, $announcement->getCreator());
        $this->assertEquals($data["title"], $announcement->getTitle());
        $this->assertEquals($data["type"], $announcement->getType());
        $this->assertEquals($data["startDate"], $announcement->getStartDate()->format($this->dateFormat));

        $this->assertEquals($announcement, $creator->getAnnouncement());
    }


    public function testCreateAnnouncementWithInvalidData() {
        self::$logger->info("Test creating an announcement with invalid data");

        $data = array ("title" => "Announcement test");
        /** @var User */
        $creator = $this->userManager->read(3);

        $this->expectException(InvalidFormDataException::class);

        $this->announcementManager->create($creator, $data);

        $this->assertNull($creator->getAnnouncement());
    }


    public function testCreateDoubleAnnouncement() {
        self::$logger->info("Test creating an announcement with invalid data");

        $data = array ("title" => "Announcement test");
        /** @var User */
        $creator = $this->userManager->read(4);

        $this->expectException(UnprocessableEntityHttpException::class);

        $this->announcementManager->create($creator, $data);
    }


    public function testListAnnouncements() {
        self::$logger->info("Test listing announcements");

        $announcements = $this->announcementManager->list(new AnnouncementFilter());

        $this->assertNotNull($announcements);

        foreach ($announcements as $announcement) {
            $this->assertInstanceOf(Announcement::class, $announcement);
        }
    }


    public function testReadAnnouncement() {
        self::$logger->info("Test reading announcement");

        $announcement = $this->announcementManager->read(1);

        $this->assertNotNull($announcement);
        $this->assertEquals(1, $announcement->getId());
    }


    public function testReadAnnouncementWithFailure() {
        self::$logger->info("Test reading announcement with failure");

        $this->expectException(AnnouncementNotFoundException::class);

        $this->announcementManager->read(999);
    }


    public function testUpdateAnnouncement() {
        self::$logger->info("Test updating announcement");

        $announcement = $this->announcementManager->read(1);
        $this->assertNotNull($announcement);

        $data = $this->announcementToArray($announcement);
        $data["maxPrice"] = 800;
        $data["description"] = "Modified announcement from test";

        $updatedAnnouncement = $this->announcementManager->update($announcement, $data);

        $this->assertEquals($announcement->getId(), $updatedAnnouncement->getId());
        $this->assertEquals($data["maxPrice"], $updatedAnnouncement->getMaxPrice());
        $this->assertEquals($data["description"], $updatedAnnouncement->getDescription());
    }


    public function testPartialUpdateAnnouncement() {
        self::$logger->info("Test partial updating announcement");

        $announcement = $this->announcementManager->read(1);
        $this->assertNotNull($announcement);

        $data = array ("endDate" => "08/09/2017");

        $updatedAnnouncement = $this->announcementManager->partialUpdate($announcement, $data);

        $this->assertEquals($data["endDate"], $updatedAnnouncement->getEndDate()->format($this->dateFormat));
    }


    public function testDeleteAnnouncement() {
        self::$logger->info("Test deleting announcement");

        $announcement = $this->announcementManager->read(2);
        $this->assertNotNull($announcement);

        $this->announcementManager->delete($announcement);

        $this->expectException(AnnouncementNotFoundException::class);
        $this->announcementManager->read(2);
    }


    private function announcementToArray(Announcement $announcement) {
        return array (
            "title" => $announcement->getTitle(),
            "type" => $announcement->getType(),
            "minPrice" => $announcement->getMinPrice(),
            "description" => $announcement->getDescription(),
            "startDate" => $announcement->getStartDate()->format($this->dateFormat),
            "location" => $announcement->getLocation()->getFormattedAddress());
    }

}