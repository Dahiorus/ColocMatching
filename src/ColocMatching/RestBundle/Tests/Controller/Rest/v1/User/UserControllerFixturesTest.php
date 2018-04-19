<?php

namespace ColocMatching\RestBundle\Tests\Controller\Rest\v1\User;

use ColocMatching\RestBundle\Tests\DataFixturesControllerTest;

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
            "size" => 5
        );
    }


    protected function invalidSearchFilter() : array
    {
        return array (
            "status" => array ("unknown", "vacation"),
            "hasGroup" => "test"
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
