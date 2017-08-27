<?php

namespace ColocMatching\RestBundle\Tests\Controller\Rest\v1\Visit;

class UsersVisitControllerTest extends VisitControllerTest {

    protected function setUp() {
        $this->visitableType = "user";
        $this->managerId = "coloc_matching.core.user_visit_manager";

        parent::setUp();
    }
}