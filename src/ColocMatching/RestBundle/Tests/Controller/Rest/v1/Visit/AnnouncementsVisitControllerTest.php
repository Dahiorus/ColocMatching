<?php

namespace ColocMatching\RestBundle\Tests\Controller\Rest\v1\Visit;

class AnnouncementsVisitControllerTest extends VisitControllerTest {

    protected function setUp() {
        $this->visitableType = "announcement";
        $this->managerId = "coloc_matching.core.announcement_visit_manager";

        parent::setUp();
    }
}