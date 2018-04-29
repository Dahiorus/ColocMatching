<?php

namespace ColocMatching\RestBundle\Tests\Controller\Rest\v1\Announcement;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\RestBundle\Tests\DataFixturesControllerTest;

class AnnouncementControllerFixturesTest extends DataFixturesControllerTest
{
    protected function setUp()
    {
        parent::setUp();
        static::$client = static::initClient();
    }


    protected function baseEndpoint() : string
    {
        return "/rest/announcements";
    }


    protected function searchFilter() : array
    {
        return array (
            "withDescription" => true,
            "status" => Announcement::STATUS_ENABLED,
            "size" => 5
        );
    }


    protected function invalidSearchFilter() : array
    {
        return array (
            "status" => "unknown",
            "address" => "azertyui"
        );
    }


    protected function searchResultAssertCallable() : callable
    {
        return function (array $announcement) {
            $status = $announcement["status"];
            self::assertTrue($status == Announcement::STATUS_ENABLED);
            self::assertNotEmpty($announcement["description"]);
        };
    }

}
