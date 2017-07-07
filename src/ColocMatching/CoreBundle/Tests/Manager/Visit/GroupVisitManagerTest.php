<?php

namespace ColocMatching\CoreBundle\Tests\Manager\Visit;

use ColocMatching\CoreBundle\Entity\Group\Group;

class GroupVisitManagerTest extends VisitManagerTest {

    protected function setUp() {
        $this->visitedClass = Group::class;

        parent::setUp();
    }
}