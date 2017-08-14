<?php

namespace ColocMatching\CoreBundle\Tests\Repository\Visit;


use ColocMatching\CoreBundle\Entity\Visit\UserVisit;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;

class UserVisitRepositoryTest extends VisitRepositoryTest {

    protected function setUp() {
        parent::setUp();
        $this->repository = self::getRepository(UserVisit::class);
    }


    public function testFindByVisited() {
        $this->logger->info("Test finding visits by visited");

        $filter = new PageableFilter();
        $visited = $this->userRepository->findById(1);

        $this->assertNotNull($visited);

        $visits = $this->repository->findByVisited($visited, $filter);

        $this->assertNotNull($visits);
        $this->assertTrue(count($visits) <= $filter->getSize());

        foreach ($visits as $visit) {
            $this->assertEquals($visited, $visit->getVisited());
        }
    }

}