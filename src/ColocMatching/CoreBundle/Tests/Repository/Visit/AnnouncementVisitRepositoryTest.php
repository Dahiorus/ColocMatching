<?php

namespace ColocMatching\CoreBundle\Tests\Repository\Visit;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Visit\AnnouncementVisit;
use ColocMatching\CoreBundle\Repository\Announcement\AnnouncementRepository;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;

class AnnouncementVisitRepositoryTest extends VisitRepositoryTest {

    /**
     * @var AnnouncementRepository
     */
    private $announcementRepository;

    protected function setUp() {
        parent::setUp();
        $this->announcementRepository = self::getRepository(Announcement::class);
        $this->repository = self::getRepository(AnnouncementVisit::class);
    }


    public function testFindByVisited() {
        $this->logger->info("Test finding visits by visited");

        $filter = new PageableFilter();
        $visited = $this->announcementRepository->findById(1);

        $this->assertNotNull($visited);

        $visits = $this->repository->findByVisited($visited, $filter);

        $this->assertNotNull($visits);
        $this->assertTrue(count($visits) <= $filter->getSize());

        foreach ($visits as $visit) {
            $this->assertEquals($visited, $visit->getVisited());
        }
    }
}