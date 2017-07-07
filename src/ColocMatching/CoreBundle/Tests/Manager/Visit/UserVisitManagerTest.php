<?php

namespace ColocMatching\CoreBundle\Tests\Manager\Visit;

use ColocMatching\CoreBundle\Entity\User\User;

class UserVisitManagerTest extends VisitManagerTest {

    protected function setUp() {
        $this->visitedClass = User::class;

        parent::setUp();
    }
}