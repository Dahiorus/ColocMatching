<?php

namespace ColocMatching\RestBundle\Tests\Controller\Rest\v1\User;

use ColocMatching\RestBundle\Tests\DataFixturesControllerTest;
use Symfony\Component\HttpFoundation\Response;

class UserControllerFixturesTest extends DataFixturesControllerTest
{
    protected function setUp()
    {
        parent::setUp();
        static::$client = static::initClient();
    }


    /**
     * @test
     */
    public function getOnePageShouldReturn206()
    {
        static::$client->request("GET", "/rest/users");
        self::assertStatusCode(Response::HTTP_PARTIAL_CONTENT);
    }


    /**
     * @test
     */
    public function getAllShouldReturn200()
    {
        static::$client->request("GET", "/rest/users", array ("size" => 100));
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function searchOnePageShouldReturn206AndFilteredResult()
    {
        static::$client->request("POST", "/rest/users/searches", array (
            "hasAnnouncement" => true,
            "status" => array ("enabled", "vacation"),
            "size" => 5
        ));
        self::assertStatusCode(Response::HTTP_PARTIAL_CONTENT);

        $content = $this->getResponseContent();
        self::assertNotNull($content);
        $users = $content["content"];

        array_walk($users, function (array $user) {
            $status = $user["status"];
            self::assertTrue($status == "enabled" || $status == "vacation");
            self::assertNotEmpty($user["_links"]["announcement"]);
        });
    }


    /**
     * @test
     */
    public function searchLastPageShouldReturn200AndFilteredResult()
    {
        static::$client->request("POST", "/rest/users/searches", array (
            "hasAnnouncement" => false,
            "status" => array ("enabled", "vacation"),
            "size" => 10,
            "page" => 5
        ));
        self::assertStatusCode(Response::HTTP_OK);

        $content = $this->getResponseContent();
        self::assertNotNull($content);
        $users = $content["content"];

        array_walk($users, function (array $user) {
            $status = $user["status"];
            self::assertTrue($status == "enabled" || $status == "vacation");
            self::assertEmpty($user["_links"]["announcement"]);
        });
    }


    /**
     * @test
     */
    public function searchWithInvalidFilterShouldReturn422()
    {
        static::$client->request("POST", "/rest/users/searches", array (
            "status" => array ("unknown", "vacation"),
            "hasGroup" => "test"
        ));
        self::assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

}
