<?php

namespace ColocMatching\CoreBundle\Tests\Manager\Visit;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;

class AnnouncementVisitManagerTest extends VisitManagerTest {

    protected function setUp() {
        $this->visitedClass = Announcement::class;

        parent::setUp();
    }
}