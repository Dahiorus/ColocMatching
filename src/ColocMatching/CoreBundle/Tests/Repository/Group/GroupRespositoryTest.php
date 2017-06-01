<?php

namespace ColocMatching\CoreBundle\Tests\Repository\Group;

use ColocMatching\CoreBundle\Tests\TestCase;
use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Repository\Group\GroupRepository;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Repository\Filter\GroupFilter;
use Psr\Log\LoggerInterface;

class GroupRespositoryTest extends TestCase {

    /**
     * @var GroupRepository
     */
    private $repository;

    /**
     * @var LoggerInterface
     */
    private $logger;


    protected function setUp() {
        $this->repository = self::getRepository(Group::class);
        $this->logger = self::getContainer()->get("logger");
    }


    protected function tearDown() {
    }


    public function testFindByPageable() {
        $this->logger->info("Test finding groups with pagination");

        $filter = new PageableFilter();
        $groups = $this->repository->findByPageable($filter);

        $this->assertNotNull($groups);
        $this->assertTrue(count($groups) <= $filter->getSize());
    }


    public function testSelectFieldsByPageable() {
        $this->logger->info("Test selecting fields of groups with pagination");

        $fields = array ("name", "id");
        $filter = new PageableFilter();
        $groups = $this->repository->findByPageable($filter, $fields);

        $this->assertNotNull($groups);
        $this->assertTrue(count($groups) <= $filter->getSize());

        foreach ($groups as $group) {
            $this->assertArrayHasKey("name", $group);
            $this->assertArrayHasKey("id", $group);
            $this->assertArrayNotHasKey("description", $group);
        }
    }


    public function testFindById() {
        $this->logger->info("Test finding one group by Id");

        $group = $this->repository->findById(1);

        $this->assertNotNull($group);
        $this->assertInstanceOf(Group::class, $group);
        $this->assertEquals(1, $group->getId());
    }


    public function testSelectFieldsById() {
        $this->logger->info("Test select fields from one group by Id");

        $fields = array ("name", "id");
        $group = $this->repository->findById(1, $fields);

        $this->assertNotNull($group);
        $this->assertEquals(1, $group["id"]);
        $this->assertArrayHasKey("name", $group);
        $this->assertArrayNotHasKey("description", $group);
    }


    public function testFindByFilter() {
        $this->logger->info("Test finding groups by filter");

        $filter = new GroupFilter();
        $groups = $this->repository->findByFilter($filter);

        $this->assertNotNull($groups);
        $this->assertTrue(count($groups) <= $filter->getSize());
    }


    public function testSelectFieldsByFilter() {
        $this->logger->info("Test selecting fields of groups by filter");

        $fields = array ("name", "id");
        $filter = new GroupFilter();
        $groups = $this->repository->findByFilter($filter, $fields);

        $this->assertNotNull($groups);
        $this->assertTrue(count($groups) <= $filter->getSize());

        foreach ($groups as $group) {
            $this->assertArrayHasKey("name", $group);
            $this->assertArrayHasKey("id", $group);
            $this->assertArrayNotHasKey("description", $group);
        }
    }

}