<?php

namespace ColocMatching\CoreBundle\Tests\Repository\Visit;


use ColocMatching\CoreBundle\Entity\Visit\UserVisit;

class UserVisitRepositoryTest extends VisitRepositoryTest {

    public function setUp() {
        parent::setUp();
        $this->repository = self::getRepository(UserVisit::class);
    }
}