<?php

namespace App\Tests\Rest\Controller\Rest\v1\Announcement;

use App\Core\Entity\Announcement\Announcement;
use App\Core\Repository\Filter\Pageable\Order;
use App\Tests\Rest\DataFixturesControllerTest;

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
            "pageable" => array (
                "size" => 5,
                "sorts" => array (
                    array ("property" => "createdAt", "direction" => Order::ASC)
                )
            )
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
