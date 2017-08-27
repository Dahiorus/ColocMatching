<?php

namespace ColocMatching\RestBundle\Tests\Controller\Rest\v1\Visit;

class GroupsVisitControllerTest extends VisitControllerTest {

    protected function setUp() {
        $this->visitableType = "group";
        $this->managerId = "coloc_matching.core.group_visit_manager";

        parent::setUp();
    }
}