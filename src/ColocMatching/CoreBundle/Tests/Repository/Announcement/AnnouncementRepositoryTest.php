<?php

namespace ColocMatching\CoreBundle\Tests\Repository\Announcement;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Repository\Announcement\AnnouncementRepository;
use ColocMatching\CoreBundle\Repository\Filter\AnnouncementFilter;
use ColocMatching\CoreBundle\Tests\TestCase;

class AnnouncementRepositoryTest extends TestCase {

    /**
     * @var AnnouncementRepository
     */
    private $repository;


    protected function setUp() {
        $this->repository = self::getEntityManager()->getRepository(Announcement::class);
    }


    public function testFindByPage() {
        self::$logger->info("Test finding announcements by page");

        /** @var AbstractFilter */
        $filter = new AnnouncementFilter();
        /** @var array */
        $announcements = $this->repository->findByPage($filter);

        $this->assertNotNull($announcements);
        $this->assertTrue(count($announcements) <= $filter->getSize());
    }


    public function testSelectFieldsByPage() {
        self::$logger->info("Test selecting announcements fields by page");

        /** @var AbstractFilter */
        $filter = new AnnouncementFilter();
        /** @var array */
        $announcements = $this->repository->selectFieldsByPage([ "id", "title"], $filter);

        $this->assertNotNull($announcements);

        foreach ($announcements as $announcement) {
            $this->assertArrayHasKey("id", $announcement);
            $this->assertArrayHasKey("title", $announcement);
            $this->assertArrayNotHasKey("rentPrice", $announcement);
        }
    }


    public function testSelectFieldsFromOne() {
        self::$logger->info("Test selecting one announcement fields by page");

        /** @var array */
        $announcement = $this->repository->selectFieldsFromOne(1, [ "id", "description"]);

        $this->assertNotNull($announcement);
        $this->assertEquals(1, $announcement["id"]);
        $this->assertArrayHasKey("id", $announcement);
        $this->assertArrayHasKey("description", $announcement);
        $this->assertArrayNotHasKey("title", $announcement);
    }


    public function testFindByFilter() {
        self::$logger->info("Test finding announcements by filter");

        /** @var AnnouncementFilter */
        $filter = new AnnouncementFilter();
        $filter->setRentPriceStart(500);
        /** @var array */
        $announcements = $this->repository->findByFilter($filter);
        $count = $this->repository->countByFilter($filter);

        $this->assertNotNull($announcements);
        $this->assertEquals(count($announcements), $count);

        foreach ($announcements as $announcement) {
            $rentPrice = $announcement->getRentPrice();

            $this->assertTrue($rentPrice >= 500);
        }
    }


    public function testSelectFieldsByFilter() {
        self::$logger->info("Test selecting announcements fields by filter");

        /** @var AnnouncementFilter */
        $filter = new AnnouncementFilter();
        $filter->setRentPriceStart(500);
        /** @var array */
        $announcements = $this->repository->selectFieldsByFilter($filter, [ "id", "rentPrice"]);

        $this->assertNotNull($announcements);
        foreach ($announcements as $announcement) {
            $rentPrice = $announcement["rentPrice"];

            $this->assertTrue($rentPrice >= 500);
            $this->assertArrayHasKey("id", $announcement);
            $this->assertArrayNotHasKey("title", $announcement);
        }
    }

}