<?php

namespace App\Tests\Rest\Controller\v1\User;

use App\Core\Repository\Filter\Pageable\Order;
use App\Tests\Rest\DataFixturesControllerTest;

class UserControllerFixturesTest extends DataFixturesControllerTest
{
    protected function setUp()
    {
        parent::setUp();
        static::$client = static::initClient();
    }


    protected function baseEndpoint() : string
    {
        return "/rest/users";
    }


    protected function searchFilter() : array
    {
        return array (
            "hasAnnouncement" => true,
            "status" => array ("enabled", "vacation"),
            "pageable" => array (
                "size" => 5,
                "sorts" => array (
                    array ("property" => "email", "direction" => Order::ASC)
                )
            )
        );
    }


    protected function invalidSearchFilter() : array
    {
        return array (
            "status" => array ("unknown", "vacation"),
            "hasGroup" => "test",
            "pageable" => "sdfklsdkl"
        );
    }


    protected function searchResultAssertCallable() : callable
    {
        return function (array $user) {
            $status = $user["status"];
            self::assertTrue($status == "enabled" || $status == "vacation");
            self::assertNotEmpty($user["_links"]["announcement"]);
        };
    }

}
