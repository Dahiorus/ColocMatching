<?php

namespace ColocMatching\CoreBundle\Tests\Repository\Visit;

use ColocMatching\CoreBundle\Entity\Visit\Visit;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Repository\Filter\VisitFilter;
use ColocMatching\CoreBundle\Repository\Visit\VisitRepository;
use ColocMatching\CoreBundle\Tests\TestCase;
use Psr\Log\LoggerInterface;

abstract class VisitRepositoryTest extends TestCase {

    /**
     * @var VisitRepository
     */
    protected $repository;

    /**
     * @var LoggerInterface
     */
    protected $logger;


    protected function setUp() {
        $this->logger = self::getContainer()->get("logger");
    }


    protected function tearDown() {
        $this->logger->info("End test");
    }


    public function testFindByPageable() {
        $this->logger->info("Test finding visits with pagination");

        $filter = new PageableFilter();
        $visits = $this->repository->findByPageable($filter);

        $this->assertNotNull($visits);
        $this->assertTrue(count($visits) <= $filter->getSize());
    }


    public function testSelectFieldsByPageable() {
        $this->logger->info("Test selecting fields of visits with pagination");

        $fields = array ("id", "visitedAt");
        $filter = new PageableFilter();
        $visits = $this->repository->findByPageable($filter, $fields);

        $this->assertNotNull($visits);
        $this->assertTrue(count($visits) <= $filter->getSize());

        foreach ($visits as $visit) {
            $this->assertArrayHasKey("visitedAt", $visit);
            $this->assertArrayHasKey("id", $visit);
            $this->assertArrayNotHasKey("description", $visit);
        }
    }


    public function testFindById() {
        $this->logger->info("Test finding one visit by Id");

        $visit = $this->repository->findById(1);

        $this->assertNotNull($visit);
        $this->assertInstanceOf(Visit::class, $visit);
        $this->assertEquals(1, $visit->getId());
    }


    public function testSelectFieldsById() {
        $this->logger->info("Test select fields from one visit by Id");

        $fields = array ("visitedAt", "id");
        $visit = $this->repository->findById(1, $fields);

        $this->assertNotNull($visit);
        $this->assertEquals(1, $visit["id"]);
        $this->assertArrayHasKey("visitedAt", $visit);
        $this->assertArrayNotHasKey("description", $visit);
    }


    public function testFindByFilter() {
        $this->logger->info("Test finding visits by filter");

        $filter = new VisitFilter();
        $visits = $this->repository->findByFilter($filter);

        $this->assertNotNull($visits);
        $this->assertTrue(count($visits) <= $filter->getSize());
    }


    public function testSelectFieldsByFilter() {
        $this->logger->info("Test selecting fields of visits by filter");

        $fields = array ("visitedAt", "id");
        $filter = new VisitFilter();
        $visits = $this->repository->findByFilter($filter, $fields);

        $this->assertNotNull($visits);
        $this->assertTrue(count($visits) <= $filter->getSize());

        foreach ($visits as $visit) {
            $this->assertArrayHasKey("visitedAt", $visit);
            $this->assertArrayHasKey("id", $visit);
            $this->assertArrayNotHasKey("description", $visit);
        }
    }

}