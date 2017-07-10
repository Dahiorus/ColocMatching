<?php

namespace ColocMatching\CoreBundle\Tests\Repository\Announcement;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Repository\Announcement\AnnouncementRepository;
use ColocMatching\CoreBundle\Repository\Filter\AnnouncementFilter;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Tests\TestCase;
use Psr\Log\LoggerInterface;

class AnnouncementRepositoryTest extends TestCase {

    /**
     * @var AnnouncementRepository
     */
    private $repository;

    /**
     * @var LoggerInterface
     */
    private $logger;


    protected function setUp() {
        $this->repository = self::getRepository(Announcement::class);
        $this->logger = self::getContainer()->get("logger");
    }


    protected function tearDown() {
    }


    public function testFindByPageable() {
        $this->logger->info("Test finding announcement with pagination");

        $filter = new PageableFilter();
        $announcements = $this->repository->findByPageable($filter);

        $this->assertNotNull($announcements);
        $this->assertTrue(count($announcements) <= $filter->getSize());
    }


    public function testSelectFieldsByPageable() {
        $this->logger->info("Test selecting fields of announcements with pagination");

        $fields = array ("title", "id");
        $filter = new PageableFilter();
        $announcements = $this->repository->findByPageable($filter, $fields);

        $this->assertNotNull($announcements);
        $this->assertTrue(count($announcements) <= $filter->getSize());

        foreach ($announcements as $announcement) {
            $this->assertArrayHasKey("title", $announcement);
            $this->assertArrayHasKey("id", $announcement);
            $this->assertArrayNotHasKey("description", $announcement);
        }
    }


    public function testFindById() {
        $this->logger->info("Test finding one announcement by Id");

        $announcement = $this->repository->findById(1);

        $this->assertNotNull($announcement);
        $this->assertInstanceOf(Announcement::class, $announcement);
        $this->assertEquals(1, $announcement->getId());
    }


    public function testSelectFieldsById() {
        $this->logger->info("Test select fields from one announcement by Id");

        $fields = array ("title", "id");
        $announcement = $this->repository->findById(1, $fields);

        $this->assertNotNull($announcement);
        $this->assertEquals(1, $announcement["id"]);
        $this->assertArrayHasKey("title", $announcement);
        $this->assertArrayNotHasKey("description", $announcement);
    }


    public function testFindByFilter() {
        $this->logger->info("Test finding announcements by filter");

        $filter = new AnnouncementFilter();
        $announcements = $this->repository->findByFilter($filter);

        $this->assertNotNull($announcements);
        $this->assertTrue(count($announcements) <= $filter->getSize());
    }


    public function testSelectFieldsByFilter() {
        $this->logger->info("Test selecting fields of announcements by filter");

        $fields = array ("title", "id");
        $filter = new AnnouncementFilter();
        $announcements = $this->repository->findByFilter($filter, $fields);

        $this->assertNotNull($announcements);
        $this->assertTrue(count($announcements) <= $filter->getSize());

        foreach ($announcements as $announcement) {
            $this->assertArrayHasKey("title", $announcement);
            $this->assertArrayHasKey("id", $announcement);
            $this->assertArrayNotHasKey("description", $announcement);
        }
    }

}