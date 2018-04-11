<?php

namespace ColocMatching\RestBundle\Tests\Controller\Rest\v1\Announcement;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\RestBundle\Tests\DataFixturesControllerTest;
use Symfony\Component\HttpFoundation\Response;

class AnnouncementControllerFixturesTest extends DataFixturesControllerTest
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
        static::$client->request("GET", "/rest/announcements", array ("page" => 2, "size" => 10, "order" => "asc"));
        self::assertStatusCode(Response::HTTP_PARTIAL_CONTENT);
    }


    /**
     * @test
     */
    public function getAllShouldReturn200()
    {
        static::$client->request("GET", "/rest/announcements", array ("size" => 100));
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function searchOnePageShouldReturn206AndFilteredResult()
    {
        static::$client->request("POST", "/rest/announcements/searches", array (
            "withDescription" => true,
            "status" => Announcement::STATUS_ENABLED,
            "size" => 5
        ));
        self::assertStatusCode(Response::HTTP_PARTIAL_CONTENT);

        $content = $this->getResponseContent();
        self::assertNotNull($content);
        $announcements = $content["content"];

        array_walk($announcements, function (array $announcement) {
            $status = $announcement["status"];
            self::assertTrue($status == Announcement::STATUS_ENABLED);
            self::assertNotEmpty($announcement["description"]);
        });
    }


    /**
     * @test
     */
    public function searchLastPageShouldReturn200AndFilteredResult()
    {
        static::$client->request("POST", "/rest/announcements/searches", array (
            "withDescription" => true,
            "status" => Announcement::STATUS_ENABLED,
            "size" => 5,
            "page" => 5
        ));
        self::assertStatusCode(Response::HTTP_OK);

        $content = $this->getResponseContent();
        self::assertNotNull($content);
        $announcements = $content["content"];

        array_walk($announcements, function (array $announcement) {
            $status = $announcement["status"];
            self::assertTrue($status == Announcement::STATUS_ENABLED);
            self::assertNotEmpty($announcement["description"]);
        });
    }


    /**
     * @test
     */
    public function searchWithInvalidFilterShouldReturn422()
    {
        static::$client->request("POST", "/rest/announcements/searches", array (
            "status" => "unknown",
            "address" => "azertyui"
        ));
        self::assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

}