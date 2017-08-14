<?php

namespace ColocMatching\CoreBundle\Tests\Repository\Visit;

use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\Visit\GroupVisit;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Repository\Group\GroupRepository;

class GroupVisitRepositoryTest extends VisitRepositoryTest {

    /**
     * @var GroupRepository
     */
    private $groupRepository;


    protected function setUp() {
        parent::setUp();
        $this->groupRepository = self::getRepository(Group::class);
        $this->repository = self::getRepository(GroupVisit::class);
    }


    public function testFindByVisited() {
        $this->logger->info("Test finding visits by visited");

        $filter = new PageableFilter();
        $visited = $this->groupRepository->findById(1);

        $this->assertNotNull($visited);

        $visits = $this->repository->findByVisited($visited, $filter);

        $this->assertNotNull($visits);
        $this->assertTrue(count($visits) <= $filter->getSize());

        foreach ($visits as $visit) {
            $this->assertEquals($visited, $visit->getVisited());
        }
    }
}