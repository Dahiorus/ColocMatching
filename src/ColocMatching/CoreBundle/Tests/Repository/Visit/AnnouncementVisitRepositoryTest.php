<?php

namespace ColocMatching\CoreBundle\Tests\Repository\Visit;


use ColocMatching\CoreBundle\Entity\Visit\AnnouncementVisit;

class AnnouncementVisitRepositoryTest extends VisitRepositoryTest {

    public function setUp() {
        parent::setUp();
        $this->repository = self::getRepository(AnnouncementVisit::class);
    }
}