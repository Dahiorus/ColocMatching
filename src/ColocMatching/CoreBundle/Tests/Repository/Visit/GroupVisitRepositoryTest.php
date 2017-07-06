<?php
/**
 * Created by PhpStorm.
 * User: Dahiorus
 * Date: 06/07/2017
 * Time: 21:10
 */

namespace ColocMatching\CoreBundle\Tests\Repository\Visit;


use ColocMatching\CoreBundle\Entity\Visit\GroupVisit;

class GroupVisitRepositoryTest extends VisitRepositoryTest {

    public function setUp() {
        parent::setUp();
        $this->repository = self::getRepository(GroupVisit::class);
    }
}